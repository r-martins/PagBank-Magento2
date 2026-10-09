<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use RicardoMartins\PagBank\Api\Connect\ConnectInterface;
use RicardoMartins\PagBank\Model\Partner\Detector;

class Config extends BaseConfig implements ConfigInterface
{
    /**
     * Base method code
     */
    public const METHOD_CODE = 'ricardomartins_pagbank';

    /**
     * Config input name for connect key
     */
    public const CONFIG_CONNECT_KEY = 'connect_key';

    /**
     * Config input name for public key
     */
    public const CONFIG_PUBLIC_KEY = 'public_key';

    /**
     * Config input name for document from
     */
    public const CONFIG_DOCUMENT_FROM = 'document_from';

    /**
     * Config input name for debug
     */
    public const CONFIG_DEBUG = 'debug';

    /**
     * @param EncryptorInterface $encryptor
     * @param ProductMetadataInterface $productMetadata
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        private readonly EncryptorInterface $encryptor,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly ReadFactory $readFactory,
        ScopeConfigInterface $scopeConfig,
        string $methodCode = self::METHOD_CODE,
        string $pathPattern = BaseConfig::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    public function getPartnerDetector($storeId = null, ?string $connectKey = null): Detector
    {
        $key = $connectKey ?? (string) $this->getConnectKey($storeId);

        return Detector::fromKey($key);
    }

    public function isVindi($storeId = null): bool
    {
        return $this->getPartnerDetector($storeId)->isVindi();
    }

    public function getWsBaseUrl(): string
    {
        return rtrim(ConnectInterface::WS_CONNECT_BASE_URI, '/') . '/';
    }

    public function getApiV1BaseUrl(): string
    {
        return rtrim(ConnectInterface::API_V1_URI, '/') . '/';
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getOrdersEndpoint($storeId = null): string
    {
        if ($this->isVindi($storeId)) {
            return $this->appendQuery($this->getApiV1BaseUrl() . 'orders', 'debug=1');
        }

        return $this->getWsBaseUrl() . 'ws/orders';
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInterestEndpoint($storeId = null): string
    {
        if ($this->isVindi($storeId)) {
            return $this->getApiV1BaseUrl() . 'charges/fees/calculate';
        }

        return $this->getWsBaseUrl() . 'ws/charges/fees/calculate';
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getPaymentInfoEndpoint($storeId = null): string
    {
        if ($this->isVindi($storeId)) {
            return $this->getApiV1BaseUrl() . 'orders/%s';
        }

        return $this->getWsBaseUrl() . 'ws/orders/%s/';
    }

    public function getTokenizeConfigEndpoint(): string
    {
        return $this->getApiV1BaseUrl() . 'tokenize/config';
    }

    public function getConnectInfoEndpoint($storeId = null): string
    {
        if ($this->isVindi($storeId)) {
            return $this->getApiV1BaseUrl() . 'connectInfo';
        }

        return $this->getWsBaseUrl() . 'connectInfo';
    }

    public function getPaymentMethodsEndpoint($storeId = null): string
    {
        return $this->getApiV1BaseUrl() . 'payment_methods';
    }

    public function getPaymentProfilesEndpoint($storeId = null): string
    {
        return $this->getApiV1BaseUrl() . 'payment_profiles';
    }

    /**
     * @param string $action setup|enroll|validate
     */
    public function getThreeDsEndpoint(string $action, $storeId = null): string
    {
        return $this->getApiV1BaseUrl() . 'three-d-secure/sessions/' . $action;
    }

    public function getPublicKeyEndpoint($storeId = null, ?string $connectKey = null): string
    {
        $detector = $this->getPartnerDetector($storeId, $connectKey);
        if ($detector->isVindi()) {
            return $this->getTokenizeConfigEndpoint();
        }

        return $this->getWsBaseUrl() . 'ws/public-keys';
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function get3DSecureSessionEndpoint($storeId = null): string
    {
        return ConnectInterface::CHECKOUT_SDK_SESSION_ENDPOINT;
    }

    /**
     * @return string
     */
    public function getNotificationEndpoint(): string
    {
        return ConnectInterface::NOTIFICATION_ENDPOINT;
    }

    /**
     * @param null $storeId
     * @return ?string
     */
    public function getConnectKey($storeId = null): ?string
    {
        return $this->getValue(self::CONFIG_CONNECT_KEY, $storeId);
    }

    /**
     * @param null $storeId
     * @return string|null
     */
    public function getPublicKey($storeId = null): ?string
    {
        return $this->getValue(self::CONFIG_PUBLIC_KEY, $storeId);
    }

    /**
     * @param null $storeId
     * @return string|null
     */
    public function getDocumentFrom($storeId = null): ?string
    {
        return $this->getValue(self::CONFIG_DOCUMENT_FROM, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function updatePixOrdersCron($storeId = null): bool
    {
        return (bool) $this->getValue('pix_update_cron', $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function forceOrderUpdateCron($storeId = null): bool
    {
        return (bool) $this->getValue('force_order_update_cron', $storeId);
    }

    /**
     * @return bool
     */
    public function isDebugActived(): bool
    {
        return (bool) $this->getValue(self::CONFIG_DEBUG);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isSandbox($storeId = null): bool
    {
        return $this->getPartnerDetector($storeId)->isSandbox();
    }

    private function appendQuery(string $url, string $query): string
    {
        if ($query === '') {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . $query;
    }

    /**
     * @return string[]
     */
    public function getHeaders($storeId = null): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getConnectKey($storeId),
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Api-Version' => '4.0',
            'Platform' => $this->getMagentoPlatform(),
            'Platform-Version' => $this->getMagentoVersion(),
            'Module-Version' => $this->getModuleVersion()
        ];
    }

    /**
     * @param string $incrementId
     * @return string
     */
    public function getOrderHash(string $incrementId): string
    {
        return substr($this->encryptor->hash($incrementId), 0, 5);
    }

    /**
     * @return string
     */
    public function getMagentoPlatform(): string
    {
        return $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition();
    }

    /**
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @return string
     */
    public function getModuleVersion(): string
    {
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                'RicardoMartins_PagBank'
            );
            $directoryRead = $this->readFactory->create($path);
            $composerJsonData = '';
            if ($directoryRead->isFile('composer.json')) {
                $composerJsonData = $directoryRead->readFile('composer.json');
            }
            $data = json_decode($composerJsonData);
            return !empty($data->version) ? $data->version : '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
