<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Payment\Gateway\ConfigInterface;

class ConfigQrCode extends \Magento\Payment\Gateway\Config\Config implements ConfigInterface
{
    /**
     * Payment Pix method code
     */
    public const METHOD_CODE = 'ricardomartins_pagbank_pix';

    /**
     * Config input name for expiration time
     */
    public const CONFIG_EXPIRATION = 'expiration';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $methodCode = self::METHOD_CODE,
        string $pathPattern = BaseConfig::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * @param $storeId
     * @return ?string
     */
    public function getQrCodeExpiration($storeId = null): ?string
    {
        return $this->getValue('expiration_time', $storeId);
    }
}
