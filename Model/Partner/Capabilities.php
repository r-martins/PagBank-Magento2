<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Partner;

use Laminas\Http\Request;
use Magento\Framework\App\CacheInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;

/**
 * Feature flags by partner (avoids scattered if CONVD).
 */
class Capabilities
{
    private const CACHE_TTL = 3600;

    private ?bool $threeDsAvailable = null;

    public function __construct(
        private readonly Config $config,
        private readonly GeneralClient $generalClient,
        private readonly TransferBuilder $transferBuilder,
        private readonly CacheInterface $cache,
        private ?Detector $detector = null
    ) {
    }

    public function forStore($storeId = null): self
    {
        $copy = clone $this;
        $copy->detector = $this->config->getPartnerDetector($storeId);
        $copy->threeDsAvailable = null;

        return $copy;
    }

    public function detector(): Detector
    {
        return $this->detector ?? $this->config->getPartnerDetector();
    }

    public function usesGatewayToken(): bool
    {
        return $this->detector()->isVindi();
    }

    public function usesEncryptedCard(): bool
    {
        return $this->detector()->isPagBank();
    }

    /**
     * PagBank: always can attempt 3DS via SDK.
     * Vindi: only if credit_card_3ds is active (cached).
     */
    public function has3ds($storeId = null): bool
    {
        $detector = $storeId === null && $this->detector ? $this->detector : $this->config->getPartnerDetector($storeId);
        if ($detector->isPagBank()) {
            return true;
        }
        if ($this->threeDsAvailable !== null && $this->detector === $detector) {
            return $this->threeDsAvailable;
        }

        $this->threeDsAvailable = $this->probeVindi3ds($detector, $storeId);

        return $this->threeDsAvailable;
    }

    private function probeVindi3ds(Detector $detector, $storeId = null): bool
    {
        $cacheKey = 'pagbank_vindi_3ds_' . $detector->fingerprint();
        $cached = $this->cache->load($cacheKey);
        if ($cached === '1' || $cached === '0') {
            return $cached === '1';
        }

        try {
            $transfer = $this->transferBuilder
                ->setHeaders($this->config->getHeaders($storeId))
                ->setUri($this->config->getPaymentMethodsEndpoint($storeId))
                ->setMethod(Request::METHOD_GET)
                ->setBody(['query' => 'code:credit_card_3ds'])
                ->build();
            $response = $this->generalClient->placeRequest($transfer);
            $available = !empty($response['credit_card_3ds']['available']);
            $this->cache->save($available ? '1' : '0', $cacheKey, ['ricardomartins_pagbank'], self::CACHE_TTL);

            return $available;
        } catch (\Exception $e) {
            $this->cache->save('0', $cacheKey, ['ricardomartins_pagbank'], 900);

            return false;
        }
    }
}
