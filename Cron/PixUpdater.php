<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Cron;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Psr\Log\LoggerInterface;
use RicardoMartins\PagBank\Api\Connect\ConsultOrderInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;

class PixUpdater
{
    public function __construct(
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly Config $config,
        private readonly ConsultOrderInterface $consultOrder,
        private readonly OrderManagementInterface $orderManagement,
        private readonly OrderResourceInterface $orderResource,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly TimezoneInterface $timezone,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->config->updatePixOrdersCron()) {
            return;
        }

        $orderCollection = $this->getPagBankPixOrders();

        foreach ($orderCollection->getItems() as $order) {
            try {
                $payment = $order->getPayment();
                $additionalInformation = $payment->getAdditionalInformation();
                $expirationDate = $additionalInformation['expiration_date'] ?? null;
                if (!$expirationDate) {
                    continue;
                }

                $expirationDate = $this->timezone->date($expirationDate);
                $expirationDate->modify('+1 day');
                $now = $this->timezone->date();
                $expired = !$expirationDate->diff($now)->invert;
                if (!$expired) {
                    continue;
                }

                $pagBankOrderId = $this->getTransactionId($payment);
                $pagBankResponse = $this->consultOrder->execute($pagBankOrderId);
                $charges = $pagBankResponse[ResponseInterface::CHARGES] ?? null;
                if ($charges) {
                    $statusCharge = $charges[0][ResponseInterface::CHARGE_STATUS] ?? null;
                    if ($statusCharge === ResponseInterface::STATUS_PAID) {
                        $payment->update(true);
                        $this->orderResource->save($order);
                        continue;
                    }
                }

                $this->orderManagement->cancel($order->getId());
            } catch (\Exception $e) {
                $this->logger->error('Error process order ' . $order->getIncrementId() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @return Collection
     */
    private function getPagBankPixOrders(): Collection
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', ['nin' => ['processing', 'canceled', 'closed', 'complete']])
            ->addFieldToFilter('state', ['nin' => ['processing', 'canceled', 'closed', 'complete']]);

        $collection->getSelect()
            ->join(
                ['payment' => $collection->getTable('sales_order_payment')],
                'main_table.entity_id = payment.parent_id',
                ['method']
            )->where('payment.method IN (?)', [ConfigQrCode::METHOD_CODE]);

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

        if (!$transaction) {
            $transaction = $this->transactionRepository->getByTransactionType(
                TransactionInterface::TYPE_CAPTURE,
                $payment->getId()
            );
        }

        return $transaction->getTxnId();
    }
}
