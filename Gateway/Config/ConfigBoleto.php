<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Payment\Gateway\ConfigInterface;

class ConfigBoleto extends BaseConfig implements ConfigInterface
{
    /**
     * Payment Boleto method code
     */
    public const METHOD_CODE = 'ricardomartins_pagbank_boleto';

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
     * @param null $storeId
     * @return string
     */
    public function getExpirationTime($storeId = null): string
    {
        return $this->getValue('expiration_time', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstructionLineOne($storeId = null): string
    {
        return $this->getValue('instruction_line_one', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstructionLineTwo($storeId = null): string
    {
        return $this->getValue('instruction_line_two', $storeId);
    }
}
