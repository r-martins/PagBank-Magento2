<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Cron;

use Magento\Framework\Exception\InputException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use RicardoMartins\PagBank\Api\Connect\ConsultOrderInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;
use RicardoMartins\PagBank\Gateway\Response\FetchPaymentHandler;

class SalesOrderUpdater
{
    public const ORDER_STATES_ALLOWED = [
        'pending',
        'pending_payment',
        'payment_review',
        'new'
    ];

    public const ORDER_STATUS_FOR_COMMENT = [
        'CANCELED',
    ];

    public function __construct(
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly ConsultOrderInterface $consultOrder,
        private readonly Config $config,
        private readonly FetchPaymentHandler $fetchPaymentHandler,
        private readonly TransactionRepositoryInterface $transactionRepository
    ){}

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->config->forceOrderUpdateCron()) {
            return;
        }

        $orders = $this->getPagBankOrders();

        /** @var OrderInterface $orders */
        foreach ($orders as $order) {
            $state = strtolower($order->getState());

            if (
                !in_array($state, self::ORDER_STATES_ALLOWED) &&
                !in_array($order->getStatus(), self::ORDER_STATUS_FOR_COMMENT)
            ) {
                $order->setPagbankNextAutoUpdateDate(null);
                $order->save();
                continue;
            }

            $pagBankOrderId = $this->getTransactionId($order->getPayment());
            $pagBankResponse = $this->consultOrder->execute($pagBankOrderId);
            $charges = isset($pagBankResponse['charges']) ? $pagBankResponse['charges'] : null;
            $chargesUpdated = $order->getPagbankCharges() !== md5(json_encode($charges));

            if (in_array($order->getStatus(), self::ORDER_STATUS_FOR_COMMENT)) {
                if ($chargesUpdated) {
                    $order->setPagbankCharges('pagbank_charges', md5(json_encode($charges)));
                    $order->addCommentToStatusHistory(
                        __('There was an update on PagBank, but the order was not changed as it has been cancelled.'),
                        false
                    );
                }
                $order->setPagbankNextAutoUpdateDate(null);
                $order->save();
                continue;
            }

            $payment = $order->getPayment();

            if ($chargesUpdated) {
                $this->fetchPaymentHandler->handle(['payment' => $order], $pagBankResponse);
                $order->setPagbankNextAutoUpdateDate(null);
                $order->setPagbankCharges(null);
                $order->save();
                $payment->save();
                continue;
            }

            $paymentMethod = $payment->getMethod();
            $orderCreatedAt = $order->getCreatedAt();
            $orderCreatedAt = new \DateTime($orderCreatedAt);
            $now = new \DateTime('now');
            $difference = $now->diff($orderCreatedAt);

            switch ($paymentMethod) {
                case ConfigQrCode::METHOD_CODE:
                    $pixExpiration = $pagBankResponse['qr_codes'][0]['expiration_date'];
                    $pixExpiration = new \DateTime($pixExpiration);
                    $pixExpiration->modify('+1 day');
                    $expired = !$pixExpiration->diff($now)->invert;
                    if ($expired) {
                        $order->setPagbankNextAutoUpdateDate(null);
                        $order->save();
                        break;
                    }

                    $nextUpdate = $now;
                    if ($difference->days < 7) {
                        $nextUpdate->modify('+6 hours');
                    } elseif ($difference->days > 7) {
                        $nextUpdate->modify('+24 hours');
                    }
                    $order->setPagbankNextAutoUpdateDate($nextUpdate->format('Y-m-d H:i:s'));
                    $order->save();
                    break;

                case ConfigBoleto::METHOD_CODE:
                    $boletoExpiration = $pagBankResponse['charges'][0]['payment_method']['boleto']['due_date'];
                    $boletoExpiration = new \DateTime($boletoExpiration);
                    $boletoExpiration->modify('+3 days');
                    $expired = !$boletoExpiration->diff($now)->invert;
                    if ($expired) {
                        $order->setPagbankNextAutoUpdateDate(null);
                        $order->save();
                        break;
                    }

                    $nextUpdate = $now;
                    $nextUpdate->modify('+6 hours');
                    $order->setPagbankNextAutoUpdateDate($nextUpdate->format('Y-m-d H:i:s'));
                    $order->save();
                    break;

                case ConfigCc::METHOD_CODE:
                    $status = $pagBankResponse['charges'][0]['status'];
                    if ($status === 'IN_ANALYSIS') {
                        if ($difference->days < 3) {
                            $nextUpdate = $now;
                            $nextUpdate->modify('+6 hours');
                            $order->setPagbankNextAutoUpdateDate($nextUpdate->format('Y-m-d H:i:s'));
                            $order->save();
                        } elseif ($difference->days > 3) {
                            $order->setPagbankNextAutoUpdateDate(null);
                            $order->save();
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @return Collection
     */
    private function getPagBankOrders()
    {
        $now = new \DateTime('now');
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('pagbank_next_auto_update_date', ['lteq' => $now->format('Y-m-d H:i:s')]);

        $collection->getSelect()
            ->join(
                ['payment' => $collection->getTable('sales_order_payment')],
                'main_table.entity_id = payment.parent_id',
                ['method']
            )->where('payment.method LIKE ?', Config::METHOD_CODE .'%');

        return $collection->setOrder('created_at', 'ASC');
    }

    /**
     * @param $payment
     * @return mixed
     * @throws InputException
     */
    private function getTransactionId($payment): mixed
    {
        $transaction = $this->transactionRepository->getByTransactionType(
            TransactionInterface::TYPE_ORDER,
            $payment->getId()
        );

        return $transaction->getTxnId();
    }
}
