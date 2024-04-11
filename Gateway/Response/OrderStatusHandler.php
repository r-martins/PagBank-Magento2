<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use Magento\Payment\Model\InfoInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class OrderStatusHandler implements HandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment']) || !isset($handlingSubject['stateObject'])) {
            throw new \InvalidArgumentException('Invalid response from gateway');
        }

        $stateObject = $handlingSubject['stateObject'];
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
    }
}
