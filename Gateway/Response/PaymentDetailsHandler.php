<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use RicardoMartins\PagBank\Api\Connect\ConnectInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $handlingSubject['payment'];

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();

        $charges = $response['charges'][0];
        $paymetMethod = $charges['payment_method'];
        $paymentType = $paymetMethod['type'];
        $links = $charges['links'];

        $data = [
            'payment_id' => $response['id'],
            'charge_id' => $charges['id'],
            'status' => $charges['status']
        ];

        $chargeIdWithouPrefix = str_replace('CHAR_', '', $data['charge_id']);
        $transctionLink = ConnectInterface::PAGBANK_TRANSACTION_DETAILS_URL . $chargeIdWithouPrefix;
        $data['charge_link'] = $transctionLink;

        if ($paymentType === 'CREDIT_CARD') {
            $creditCard = $paymetMethod['card'];
            $data['installments'] = $paymetMethod['installments'];
            $data['cc_last_4'] = $creditCard['last_digits'];
            $data['cc_owner'] = $creditCard['holder']['name'];
        }

        if ($paymentType === 'BOLETO') {
            $boleto = $paymetMethod['boleto'];
            $data['payment_id'] = $boleto['id'];
            $data['payment_text_boleto'] = $boleto['formatted_barcode'];
            $data['expiration_date'] = $boleto['due_date'];

            foreach ($links as $link) {
                if ($link['media'] == 'application/pdf') {
                    $data['payment_link_boleto'] = $link['href'];
                    break;
                }
            }
        }

        try {
            $payment->setAdditionalInformation($data);
        } catch (\Exception $e) {}
    }
}
