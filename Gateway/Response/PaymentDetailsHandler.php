<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Model\Partner\Branding;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class PaymentDetailsHandler implements HandlerInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Branding $branding
    ) {
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

        if (!isset($response['charges'])) {
            throw new \InvalidArgumentException('Invalid response from gateway');
        }

        $charges = $response['charges'][0];
        $paymetResponse = $charges['payment_response'] ?? [];
        $paymetMethod = $charges['payment_method'] ?? [];
        $paymentType = $paymetMethod['type'] ?? '';
        $links = $charges['links'] ?? [];

        $storeId = $payment->getOrder() ? $payment->getOrder()->getStoreId() : null;
        $detector = $this->config->getPartnerDetector($storeId);
        $isSandbox = $detector->isSandbox();

        $data = [
            'payment_id' => $response['id'],
            'charge_id' => $charges['id'],
            'status' => $charges['status'],
            'partner' => $detector->getPartner(),
            'connect_key_fp' => $detector->fingerprint(),
            'is_sandbox' => $isSandbox,
        ];

        $chargeLink = $this->branding->withDetector($detector)->adminChargeUrl((string) $data['charge_id'], $isSandbox);
        if ($chargeLink) {
            $data['charge_link'] = $chargeLink;
        }

        if (is_string($paymentType) && str_starts_with($paymentType, 'CREDIT_CARD') && isset($paymetMethod['card'])) {
            $creditCard = $paymetMethod['card'];
            $data['brand'] = $creditCard['brand'] ?? '';
            $data['cc_last_4'] = $creditCard['last_digits'] ?? '';
            $data['cc_owner'] = $creditCard['holder']['name'] ?? '';
            $data['installments'] = $paymetMethod['installments'] ?? '';

            $paymentRawData = $paymetResponse['raw_data'] ?? [];
            if (is_array($paymentRawData)) {
                $data['authorization_code'] = $paymentRawData['authorization_code'] ?? '';
                $data['nsu'] = $paymentRawData['nsu'] ?? '';
            }
        }

        if ($paymentType === 'BOLETO') {
            $boleto = $paymetMethod['boleto'];
            $data['payment_text_boleto'] = $boleto['formatted_barcode'];
            $data['expiration_date'] = $boleto['due_date'];

            foreach ($links as $link) {
                if ($link['media'] == 'application/pdf') {
                    $data['payment_link_boleto_pdf'] = $link['href'];
                }
                if ($link['media'] == 'image/png') {
                    $data['payment_link_boleto_image'] = $link['href'];
                }
            }
        }

        try {
            $additionalInfo = $payment->getAdditionalInformation();
            $payment->setAdditionalInformation(array_merge($additionalInfo, $data));
        } catch (\Exception $e) {}
    }
}
