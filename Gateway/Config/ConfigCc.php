<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Payment\Gateway\ConfigInterface;

class ConfigCc extends BaseConfig implements ConfigInterface
{
    /**
     * Payment Credit Card method code
     */
    public const METHOD_CODE = 'ricardomartins_pagbank_cc';

    /**
     * Credit card custom icons key
     */
    public const CARD_BRAND_ICONS = 'icons';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param Source $assetSource
     * @param RequestInterface $request
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        private readonly Repository $assetRepo,
        private readonly Source $assetSource,
        private readonly RequestInterface $request,
        private readonly SerializerInterface $serializer,
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
        return $this->getValue('installments_options', $storeId) ?? '';
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
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $ccTypes = $this->getValue('cc_types_mapper');
        if (!$ccTypes) {
            return [];
        }

        return $this->serializer->unserialize($ccTypes);
    }

    /**
     * Create a file asset that's subject of fallback system
     *
     * @param string $fileId
     * @param array $params
     * @return \Magento\Framework\View\Asset\File
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);
        try {
            return $this->assetRepo->createAsset($fileId, $params);
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * Method to find source.
     *
     * @param $asset
     * @return bool|string
     */
    public function findSource($asset)
    {
        return $this->assetSource->findSource($asset);
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
