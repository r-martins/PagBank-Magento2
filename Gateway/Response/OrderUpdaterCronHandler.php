<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;

class OrderUpdaterCronHandler implements HandlerInterface
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly LoggerInterface $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $handlingSubject['payment'];

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();
        $charges = isset($response[ResponseInterface::CHARGES]) ? $response[ResponseInterface::CHARGES] : [];

        try {;
            $orderDate = $order->getUpdatedAt() ?? $order->getCreatedAt();
            $updateDate = new \DateTime($orderDate ?? 'now');
            $updateDate->modify('+6 hours');
            $order->setData('pagbank_next_auto_update_date', $updateDate->format('Y-m-d H:i:s'));

            if (!empty($charges)) {
                $order->setData('pagbank_charges', md5(json_encode($charges)));
            }
        } catch (\Exception $e) {
            $this->logger->error('RicardoMartins_PagBank Error: ' . $e->getMessage());
        }
    }
}
