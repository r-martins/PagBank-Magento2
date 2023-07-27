<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Payment\Gateway\ConfigInterface;

class ConfigCc extends BaseConfig implements ConfigInterface
{
    /**
     * Payment Credit Card method code
     */
    public const METHOD_CODE = 'ricardomartins_pagbank_cc';

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
    public function getSoftDescriptor($storeId = null): string
    {
        return (string) $this->getValue('soft_descriptor', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstallmentsOptions($storeId = null): string
    {
        return $this->getValue('installments_options', $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getInstallmentsWithoutInterestNumber($storeId = null): int
    {
        return (int) $this->getValue('installments_options_fixed', $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getInstallmentsMinAmount($storeId = null): int
    {
        return (int) $this->getValue('installments_options_min_total', $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabledInstallmentsLimit($storeId = null): bool
    {
        return (bool) $this->getValue('enable_installments_limit', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstallmentsLimit($storeId = null): string
    {
        return $this->getValue('installments_limit', $storeId);
    }

    /**
     * Get the max installments without interest based on order total and config options
     * Will return '' if the option is set to get from the PagBank Config, 0 if the option is set to buyer,
     * a fixed number if the option is set to fixed or the calculated number based on the order total.
     *
     * @param $amount
     * @param null $storeId
     * @return int|null
     */
    public function getMaxInstallmentsNoInterest($amount, $storeId = null): ?int
    {
        $installmentsOptions = $this->getInstallmentsOptions($storeId);

        return match ($installmentsOptions) {
            'fixed' => $this->getInstallmentsWithoutInterestNumber($storeId),
            'buyer' => 0,
            'min_total' => $this->calculeInstallmentsNumberWithMinTotal(
                $this->getInstallmentsMinAmount($storeId),
                $amount
            ),
            default => null,
        };
    }

    /**
     * @param $minTotal
     * @param $amount
     * @return int
     */
    private function calculeInstallmentsNumberWithMinTotal($minTotal, $amount): int
    {
        $installments = floor($amount / $minTotal);
        return (int) min($installments, 18);
    }
}
