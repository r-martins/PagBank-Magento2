<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;

class TransactionAdditionalInfoHandler implements HandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $handlingSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        $transactionId = $response[ResponseInterface::PAGBANK_ORDER_ID];
        $payment->setTransactionId($transactionId);
        $payment->setCcTransId($transactionId);
        $payment->setLastTransId($transactionId);

        $rawDetails = [];

        $rawDetails['pagbank_order_id'] = $transactionId;

        if (isset($response[ResponseInterface::REFERENCE_ID])) {
            $rawDetails[ResponseInterface::REFERENCE_ID] = $response[ResponseInterface::REFERENCE_ID];
        }

        $charge = isset($response[ResponseInterface::CHARGES][0]) ? $response[ResponseInterface::CHARGES][0] : [];
        if ($charge) {
            $rawDetails['charge_id'] = $charge[ResponseInterface::CHARGE_ID];
            $rawDetails['charge_status'] = $charge[ResponseInterface::CHARGE_STATUS];
        }

        $paymentResponse = isset($charge[ResponseInterface::PAYMENT_RESPONSE]) ? $response[ResponseInterface::CHARGES][0][ResponseInterface::PAYMENT_RESPONSE] : null;
        if ($paymentResponse) {
            $reference = isset($paymentResponse[ResponseInterface::PAYMENT_RESPONSE_REFERENCE]) ? $paymentResponse[ResponseInterface::PAYMENT_RESPONSE_REFERENCE] . ' - ' : null;
            $payment->setTransactionAdditionalInfo(
                'message_code_' . $paymentResponse[ResponseInterface::PAYMENT_RESPONSE_CODE],
                $reference . $paymentResponse[ResponseInterface::PAYMENT_RESPONSE_MESSAGE]
            );
        }

        $payment->setTransactionAdditionalInfo(
            'raw_details_info',
            $rawDetails
        );

        $payment->addTransaction(TransactionInterface::TYPE_ORDER);
    }
}
