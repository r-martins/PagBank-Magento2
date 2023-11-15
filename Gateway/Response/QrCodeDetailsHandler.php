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
        if (!isset($handlingSubject['payment'])) {
            throw new \InvalidArgumentException('Invalid response from gateway');
        }

        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $handlingSubject['payment'];

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();

        if (!isset($response['qr_codes'])) {
            throw new \InvalidArgumentException('Invalid response from gateway');
        }

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
