<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use RicardoMartins\PagBank\Model\Order\Status\History;

class FetchPaymentHandler implements HandlerInterface
{
    /** @var array */
    public const PENDING_PAYMENT_STATES = [
        'new',
        'payment_review',
        'pending_payment'
    ];

    /**
     * @param InvoiceSender $invoiceSender
     * @param History $statusHistory
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly InvoiceSender $invoiceSender,
        protected readonly History $statusHistory,
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
        $comment = '';
        $sendInvoiceEmail = $result = false;

        if (!isset($response[ResponseInterface::CHARGES])) {
            return;
        }

        $charge = $response[ResponseInterface::CHARGES][0];
        $pagbankPaymentId = $response[ResponseInterface::PAGBANK_ORDER_ID];
        $pagbankChargePaymentId = $charge[ResponseInterface::CHARGE_ID];

        $status = $charge[ResponseInterface::CHARGE_STATUS] ?? '';

        switch ($status) {
            case ResponseInterface::STATUS_CANCELED:
            case ResponseInterface::STATUS_DECLINED:
                $comment = "Order canceled at the payment gateway. Payment status received from the gateway is {$status}";
                $result = $this->denyOrder($payment, $pagbankPaymentId, $pagbankChargePaymentId);
                $sendOrderEmail = true;
                break;
            case ResponseInterface::STATUS_AUTHORIZED:
                $comment = "Order awaiting payment review.";
                $result = $this->authorizeOrder($payment);
                break;
            case ResponseInterface::STATUS_PAID:
                $comment = "Payment status received from the gateway is: {$status}.";
                $result = $this->approveOrder($payment, $pagbankPaymentId, $pagbankChargePaymentId);
                $sendInvoiceEmail = true;
                break;
            case ResponseInterface::STATUS_IN_ANALYSIS:
                $comment = "Payment is in analysis in the gateway.";
                $result = true;
                break;
        }

        if (!$result) {
            return;
        }

        if ($sendInvoiceEmail) {
            try {
                $invoice = $payment->getCreatedInvoice();
                if ($invoice && !$invoice->getEmailSent()) {
                    $this->invoiceSender->send($invoice, false);
                }
            } catch (\Exception $e) {
                $this->logger->critical('Error on send invoice email: [' . $order->getIncrementId() . '] ' . $e->getMessage());
            }
        }

        if ($comment) {
            try {
                $this->statusHistory->addCommentToStatusHistory($order, $comment);
            } catch (\Exception $e) {
                $this->logger->critical('Error on add comment to status history: [' . $order->getIncrementId() . '] ' . $e->getMessage());
            }
        }

    }

    /**
     * @param $payment
     * @param $transactionParentId
     * @param $transactionId
     * @return bool
     */
    protected function denyOrder($payment, $transactionParentId, $transactionId)
    {
        $order = $payment->getOrder();
        if (!in_array($order->getState(), self::PENDING_PAYMENT_STATES)) {
            return false;
        }

        try {
            $payment->setPreparedMessage(__('Order Canceled.'));
            $payment->setTransactionId($transactionId.'-deny');
            $payment->setParentTransactionId($transactionParentId);
            $payment->setNotificationResult(true);
            $payment->deny(false);
            return true;
        } catch (\Exception $e) {
            $order = $payment->getOrder();
            $this->logger->critical('Error on cancel order: [' . $order->getIncrementId() . '] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $payment
     * @return bool
     */
    protected function authorizeOrder($payment)
    {
        try {
            $payment->setIsTransactionApproved(false);
            $payment->setIsTransactionDenied(false);
            $payment->setIsInProcess(false);
            return true;
        } catch (\Exception $e) {
            $order = $payment->getOrder();
            $this->logger->critical('Error on cancel order: [' . $order->getIncrementId() . '] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $payment
     * @param $transactionParentId
     * @param $transactionId
     * @return bool
     */
    protected function approveOrder($payment, $transactionParentId, $transactionId)
    {
        $order = $payment->getOrder();
        $baseTotalDue = $order->getBaseTotalDue();
        if (!in_array($order->getState(), self::PENDING_PAYMENT_STATES)) {
            return false;
        }

        try {
            $payment->setTransactionId($transactionId . '-capture');
            $payment->setParentTransactionId($transactionParentId);
            $payment->registerAuthorizationNotification($baseTotalDue);
            $payment->registerCaptureNotification($baseTotalDue);
            $payment->setShouldCloseParentTransaction(true);
            return true;
        } catch (\Exception $e) {
            $this->logger->critical('Error on register payment: [' . $order->getIncrementId() . '] ' . $e->getMessage());
            return false;
        }
    }
}
