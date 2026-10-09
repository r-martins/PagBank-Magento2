<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Vindi;

use Laminas\Http\Request;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Store\Model\StoreManagerInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Converter\Converter;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;
use RicardoMartins\PagBank\Model\DocumentNormalizer;

/**
 * Server-side proxies for Vindi tokenize/3DS via API PB v1.
 */
class Proxy
{
    public function __construct(
        private readonly Config $config,
        private readonly GeneralClient $generalClient,
        private readonly TransferBuilder $transferBuilder,
        private readonly Converter $converter,
        private readonly StoreManagerInterface $storeManager,
        private readonly DocumentNormalizer $documentNormalizer
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function assertVindi(): void
    {
        if (!$this->config->isVindi()) {
            throw new LocalizedException(__('Vindi operation is not available for this Connect Key.'));
        }
    }

    /**
     * @param array $body
     * @return array
     * @throws LocalizedException
     */
    public function createPaymentProfile(array $body): array
    {
        $this->assertVindi();
        $token = (string) ($body['gateway_token'] ?? '');
        $company = (string) ($body['payment_company_code'] ?? 'mastercard');
        $checkout = is_array($body['checkout'] ?? null) ? $body['checkout'] : [];
        if ($token === '') {
            throw new LocalizedException(__('Card token is missing.'));
        }

        $payload = [
            'gateway_token' => $token,
            'payment_company_code' => $company,
            'payment_method_code' => 'credit_card',
            'customer' => $this->buildCustomerFromCheckout($checkout),
        ];
        $response = $this->post($this->config->getPaymentProfilesEndpoint(), $payload);
        $id = $response['payment_profile']['id'] ?? $response['id'] ?? null;
        if (!$id) {
            throw new LocalizedException(__('Could not create the payment profile.'));
        }

        return [
            'payment_profile_id' => (int) $id,
            'customer_id' => (int) ($response['customer_id'] ?? ($response['payment_profile']['customer_id'] ?? 0)),
            'payment_profile' => $response['payment_profile'] ?? $response,
        ];
    }

    /**
     * @param array $body
     * @return array
     * @throws LocalizedException
     */
    public function threeDsSetup(array $body): array
    {
        $this->assertVindi();
        $profileId = (int) ($body['payment_profile_id'] ?? 0);
        if ($profileId <= 0) {
            throw new LocalizedException(__('Invalid payment_profile_id.'));
        }

        $response = $this->post($this->config->getThreeDsEndpoint('setup'), [
            'payment_profile_id' => $profileId,
            'payment_method_code' => 'credit_card_3ds',
        ]);
        $setup = $response['setup'] ?? $response;
        if (empty($setup['session_id']) || empty($setup['access_token'])) {
            throw new LocalizedException(__('Invalid 3DS setup response.'));
        }

        return ['setup' => $setup];
    }

    /**
     * @param array $body
     * @return array
     * @throws LocalizedException
     */
    public function threeDsEnroll(array $body): array
    {
        $this->assertVindi();
        $sessionId = (string) ($body['session_id'] ?? '');
        $amount = (int) ($body['amount'] ?? 0);
        $installments = max(1, (int) ($body['installments'] ?? 1));
        $device = is_array($body['device_info'] ?? null) ? $body['device_info'] : [];
        $checkout = is_array($body['checkout'] ?? null) ? $body['checkout'] : [];
        if ($sessionId === '' || $amount < 100) {
            throw new LocalizedException(__('Incomplete 3DS enroll data.'));
        }

        $payload = $this->buildEnrollPayload($sessionId, $amount, $installments, $device, $checkout);
        $response = $this->post($this->config->getThreeDsEndpoint('enroll'), $payload);
        $enroll = $response['enroll'] ?? $response;
        if (is_array($enroll) && isset($enroll['enroll']) && is_array($enroll['enroll'])) {
            $enroll = $enroll['enroll'];
        }

        return ['enroll' => $enroll];
    }

    /**
     * @param array $body
     * @return array
     * @throws LocalizedException
     */
    public function threeDsValidate(array $body): array
    {
        $this->assertVindi();
        $txid = (string) ($body['authentication_transaction_id'] ?? '');
        $cardType = (string) ($body['card_type'] ?? 'visa');
        if ($txid === '') {
            throw new LocalizedException(__('TransactionId is missing.'));
        }

        $response = $this->post($this->config->getThreeDsEndpoint('validate'), [
            'authentication_transaction_id' => $txid,
            'card_type' => $cardType,
        ]);

        return ['validate' => $response['validate'] ?? $response];
    }

    /**
     * @param array $checkout
     * @return array
     */
    private function buildCustomerFromCheckout(array $checkout): array
    {
        $name = trim((string) ($checkout['customerName'] ?? ''));
        if ($name === '') {
            $name = 'Cliente';
        }
        $email = strtolower(trim((string) ($checkout['email'] ?? '')));
        $taxId = $this->documentNormalizer->normalize((string) ($checkout['tax_id'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($checkout['phone'] ?? '')) ?? '';
        if ($phone !== '' && !str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        return [
            'name' => $name,
            'email' => $email,
            'registry_code' => $taxId,
            'mobile_phone' => $phone,
        ];
    }

    /**
     * @param array $device
     * @param array $checkout
     * @return array
     * @throws LocalizedException
     */
    private function buildEnrollPayload(
        string $sessionId,
        int $amountCents,
        int $installments,
        array $device,
        array $checkout
    ): array {
        $customer = $this->buildCustomerFromCheckout($checkout);
        $street = trim((string) ($checkout['street'] ?? ''));
        $number = (string) ($checkout['number'] ?? 'S/N');
        $city = (string) ($checkout['city'] ?? '');
        $state = (string) ($checkout['regionCode'] ?? '');
        $postcode = preg_replace('/\D+/', '', (string) ($checkout['postalCode'] ?? '')) ?? '';
        $store = $this->storeManager->getStore();

        return [
            'session_id' => $sessionId,
            'amount' => $amountCents,
            'currency' => 'BRL',
            'installments' => $installments,
            'return_url' => $store->getUrl('pagbank/ajax/vindithreedsreturn'),
            'client_reference_code' => 'm2_' . time(),
            'device_info' => [
                'ip_address' => $this->clientIp(),
                'user_agent' => (string) ($device['user_agent'] ?? 'Mozilla/5.0'),
                'screen_height' => (int) ($device['screen_height'] ?? 1080),
                'screen_width' => (int) ($device['screen_width'] ?? 1920),
                'color_depth' => (int) ($device['color_depth'] ?? 24),
                'timezone_offset' => (int) ($device['timezone_offset'] ?? -180),
                'language' => (string) ($device['language'] ?? 'pt-BR'),
                'java_enabled' => !empty($device['java_enabled']),
            ],
            'billing_address' => [
                'name' => $customer['name'],
                'email' => $customer['email'] !== '' ? $customer['email'] : 'cliente@exemplo.test',
                'street' => $street !== '' ? $street . ($number !== '' ? ', ' . $number : '') : 'Nao informado',
                'city' => $city !== '' ? $city : 'Sao Paulo',
                'state' => $state !== '' ? $state : 'SP',
                'postal_code' => $postcode !== '' ? $postcode : '01310100',
                'country' => 'BR',
            ],
            'buyer_information' => [
                'registry_code' => $customer['registry_code'] !== '' ? $customer['registry_code'] : '00000000000',
                'mobile_phone' => $customer['mobile_phone'] !== '' ? $customer['mobile_phone'] : '5511999999999',
            ],
            'merchant_information' => [
                'merchant_name' => substr((string) $store->getFrontendName(), 0, 40) ?: 'Loja',
                'url' => $store->getBaseUrl(),
            ],
        ];
    }

    /**
     * @param array $payload
     * @return array
     * @throws LocalizedException
     */
    private function post(string $uri, array $payload): array
    {
        $transfer = $this->transferBuilder
            ->setHeaders($this->config->getHeaders())
            ->setUri($uri)
            ->setMethod(Request::METHOD_POST)
            ->setBody($this->converter->convert($payload))
            ->build();
        $response = $this->generalClient->placeRequest($transfer);
        unset($response['is_sandbox']);
        if ($response === [] || isset($response['error_messages']) || isset($response['errors'])) {
            $detail = $response['error_messages'] ?? $response['errors'] ?? $response['message'] ?? '';
            if (is_array($detail)) {
                $detail = json_encode($detail);
            }
            throw new LocalizedException(__('Vindi request failed. %1', (string) $detail));
        }

        return $response;
    }

    private function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $parts = explode(',', (string) $_SERVER[$key]);

                return trim($parts[0]);
            }
        }

        return '127.0.0.1';
    }
}
