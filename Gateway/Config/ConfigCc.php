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
     * Credit card vault method code key
     */
    public const CC_VAULT_CODE = 'ccVaultCode';

    /**
     * Credit card 3D Secure key
     */
    public const CC_THREED_SECURE = 'ccThreeDSecure';

    /**
     * Credit card 3D Secure allow to continue key
     */
    public const CC_THREED_SECURE_ALLOW_CONTINUE = 'ccThreeDSecureAllowContinue';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param Source $assetSource
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
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
     * @return bool
     */
    public function isThreeDSecureActive($storeId = null): bool
    {
        return (bool) $this->getValue('cc_3ds', $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isThreeDSecureAllowContinue($storeId = null): bool
    {
        return (bool) $this->getValue('cc_3ds_allow_continue', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstallmentsOptions($storeId = null): string
    {
        $value = (string) ($this->getValue('installments_options', $storeId) ?? '');
        // Legacy "external" (PagBank account settings) is no longer offered.
        if ($value === '' || $value === 'external') {
            return 'buyer';
        }

        return $value;
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
     * Get the max installments without interest based on order total and config options.
     * Returns 0 when the buyer pays the interest (default, including the removed PagBank-account option),
     * a fixed number for "fixed", or the number calculated from the order total for "min_total".
     *
     * @param $amount
     * @param null $storeId
     * @return int
     */
    public function getMaxInstallmentsNoInterest($amount, $storeId = null): int
    {
        $installmentsOptions = $this->getInstallmentsOptions($storeId);

        return match ($installmentsOptions) {
            'fixed' => $this->getInstallmentsWithoutInterestNumber($storeId),
            'min_total' => $this->calculeInstallmentsNumberWithMinTotal(
                $this->getInstallmentsMinAmount($storeId),
                $amount
            ),
            default => 0,
        };
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $ccTypes = $this->getValue('cctypes_pagbank_mapper');
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
