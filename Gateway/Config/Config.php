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

    /**
     * @return string
     */
    public function getOrdersEndpoint(): string
    {
        if ($this->isSandbox()) {
            return ConnectInterface::WS_ENDPOINT_ORDERS . '?' . ConnectInterface::SANDBOX_PARAM;
        }

        return ConnectInterface::WS_ENDPOINT_ORDERS;
    }

    /**
     * @return string
     */
    public function getInterestEndpoint(): string
    {
        if ($this->isSandbox()) {
            return ConnectInterface::WS_ENDPOINT_INTEREST . '?' . ConnectInterface::SANDBOX_PARAM;
        }

        return ConnectInterface::WS_ENDPOINT_INTEREST;
    }

    /**
     * @return string
     */
    public function getPaymentInfoEndpoint(): string
    {
        $endpoint = ConnectInterface::WS_ENDPOINT_PAYMENT_INFO . '/%s/';
        if ($this->isSandbox()) {
            return $endpoint . '?' . ConnectInterface::SANDBOX_PARAM;
        }

        return $endpoint;
    }

    /**
     * @return string
     */
    public function getNotificationEndpoint(): string
    {
        return ConnectInterface::NOTIFICATION_ENDPOINT;
    }

    /**
     * @return string
     */
    public function getConnectKey(): string
    {
        return $this->getValue(self::CONFIG_CONNECT_KEY);
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->getValue(self::CONFIG_PUBLIC_KEY);
    }

    /**
     * @return string
     */
    public function getDocumentFrom(): string
    {
        return $this->getValue(self::CONFIG_DOCUMENT_FROM);
    }

    /**
     * @return bool
     */
    public function isDebugActived(): bool
    {
        return (bool) $this->getValue(self::CONFIG_DEBUG);
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        $connectKey = $this->getConnectKey();
        if (str_contains($connectKey, ConnectInterface::SANDBOX_PREFIX)) {
            return true;
        }
        return false;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getConnectKey(),
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
    private function getMagentoPlatform(): string
    {
        return $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition();
    }

    /**
     * @return string
     */
    private function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @return string
     */
    private function getModuleVersion(): string
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
