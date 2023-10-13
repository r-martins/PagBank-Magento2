<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use Magento\Payment\Model\InfoInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class QrCodeDetailsHandler implements HandlerInterface
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

        $qrCodes = $response['qr_codes'][0];
        $qrCodesLinks = $qrCodes['links'];
        $data = [];

        foreach ($qrCodesLinks as $qrcodesLink) {
            if ($qrcodesLink['media'] == 'image/png') {
                $data['payment_link_qrcode'] = $qrcodesLink['href'];
            }
        }

        $data['payment_id'] = $qrCodes['id'];
        $data['payment_text_pix'] = $qrCodes['text'];
        $data['expiration_date'] = $qrCodes['expiration_date'];

        try {
            $additionalInfo = $payment->getAdditionalInformation();
            $payment->setAdditionalInformation(array_merge($additionalInfo, $data));
        } catch (\Exception $e) {}
    }
}
