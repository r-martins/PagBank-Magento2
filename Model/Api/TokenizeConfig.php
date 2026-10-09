<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Laminas\Http\Request;
use Magento\Framework\App\CacheInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;

/**
 * Cached GET /v1/tokenize/config (Vindi public key + payment_profiles URL).
 */
class TokenizeConfig
{
    public function __construct(
        private readonly Config $config,
        private readonly GeneralClient $generalClient,
        private readonly TransferBuilder $transferBuilder,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @return array{public_key:string,payment_profiles_url:string}
     */
    public function get($storeId = null): array
    {
        $detector = $this->config->getPartnerDetector($storeId);
        if (!$detector->isVindi()) {
            return ['public_key' => '', 'payment_profiles_url' => ''];
        }

        $cacheKey = 'pagbank_tokenize_' . $detector->fingerprint();
        $cached = $this->cache->load($cacheKey);
        if ($cached) {
            $decoded = json_decode($cached, true);
            if (is_array($decoded)) {
                return $this->normalize($decoded);
            }
        }

        try {
            $transfer = $this->transferBuilder
                ->setHeaders($this->config->getHeaders($storeId))
                ->setUri($this->config->getTokenizeConfigEndpoint())
                ->setMethod(Request::METHOD_GET)
                ->setBody([])
                ->build();
            $response = $this->generalClient->placeRequest($transfer);
            $normalized = $this->normalize($response);
            if ($normalized['public_key'] !== '') {
                $this->cache->save(json_encode($normalized), $cacheKey, ['ricardomartins_pagbank'], 3600);
            }

            return $normalized;
        } catch (\Exception $e) {
            return ['public_key' => '', 'payment_profiles_url' => ''];
        }
    }

    /**
     * @param array $response
     * @return array{public_key:string,payment_profiles_url:string}
     */
    private function normalize(array $response): array
    {
        $publicKey = (string) ($response['public_api_key'] ?? $response['public_key'] ?? $response['publicKey'] ?? '');
        $profilesUrl = (string) ($response['payment_profiles_url'] ?? ($response['payment_profiles']['url'] ?? ''));

        return [
            'public_key' => $publicKey,
            'payment_profiles_url' => $profilesUrl,
        ];
    }
}
