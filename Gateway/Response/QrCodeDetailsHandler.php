<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use RicardoMartins\PagBank\Gateway\Config\Config;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class QrCodeDetailsHandler implements HandlerInterface
{
    public function __construct(private readonly Config $config)
    {
    }
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
        $qrCodesLinks = $qrCodes['links'] ?? [];
        $data = [];

        foreach ($qrCodesLinks as $qrcodesLink) {
            if (!is_array($qrcodesLink)) {
                continue;
            }
            $href = (string) ($qrcodesLink['href'] ?? '');
            $media = (string) ($qrcodesLink['media'] ?? '');
            $rel = strtoupper((string) ($qrcodesLink['rel'] ?? ''));
            if ($href !== '' && ($media === 'image/png' || $rel === 'QRCODE.PNG')) {
                $data['payment_link_qrcode'] = $href;
            }
        }

        $storeId = $payment->getOrder() ? $payment->getOrder()->getStoreId() : null;
        $detector = $this->config->getPartnerDetector($storeId);
        $data['payment_id'] = $response['id'];
        $data['payment_text_pix'] = $qrCodes['text'] ?? '';
        $data['expiration_date'] = $qrCodes['expiration_date'] ?? '';
        $data['is_sandbox'] = $detector->isSandbox();
        $data['partner'] = $detector->getPartner();
        $data['connect_key_fp'] = $detector->fingerprint();

        try {
            $additionalInfo = $payment->getAdditionalInformation();
            $payment->setAdditionalInformation(array_merge($additionalInfo, $data));
        } catch (\Exception $e) {}
    }
}
