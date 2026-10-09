<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use RicardoMartins\PagBank\Api\Connect\ThreeDSecureSessionInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Config\ConfigCcVault;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;
use RicardoMartins\PagBank\Model\Api\TokenizeConfig;
use RicardoMartins\PagBank\Model\Partner\Capabilities;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var array $icons
     */
    private array $icons = [];

    public function __construct(
        private readonly Config $config,
        private readonly ConfigCc $configCc,
        private readonly ConfigBoleto $configBoleto,
        private readonly ConfigQrCode $configQrCode,
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly TokenizeConfig $tokenizeConfig,
        private readonly Capabilities $capabilities
    ) {}

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $storeId = null;

        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
        } catch (\Exception $e) {}

        $connectKey = $this->config->getConnectKey($storeId);
        $isVindi = $this->config->isVindi($storeId);
        $tokenize = $isVindi ? $this->tokenizeConfig->get($storeId) : [];
        $publicKey = !empty($tokenize['public_key']) ? $tokenize['public_key'] : $this->config->getPublicKey($storeId);

        if (empty($connectKey) || empty($publicKey)) {
            return [];
        }

        $threeDs = $this->configCc->isThreeDSecureActive($storeId);
        if ($isVindi) {
            $threeDs = $threeDs && $this->capabilities->has3ds($storeId);
        }

        $detector = $this->config->getPartnerDetector($storeId);

        return [
            'payment' => [
                Config::METHOD_CODE => [
                    Config::CONFIG_PUBLIC_KEY => $publicKey,
                    Config::CONFIG_DOCUMENT_FROM => $this->config->getDocumentFrom($storeId),
                    'is_vindi' => $isVindi,
                    'partner' => $detector->getPartner(),
                    'vindi_payment_profiles_url' => $tokenize['payment_profiles_url'] ?? '',
                    'vindi_payment_profile_endpoint' => $this->urlBuilder->getUrl('pagbank/ajax/vindipaymentprofile'),
                    'vindi_3ds_setup_endpoint' => $this->urlBuilder->getUrl('pagbank/ajax/vindithreedssetup'),
                    'vindi_3ds_enroll_endpoint' => $this->urlBuilder->getUrl('pagbank/ajax/vindithreedsenroll'),
                    'vindi_3ds_validate_endpoint' => $this->urlBuilder->getUrl('pagbank/ajax/vindithreedsvalidate'),
                    'vindi_3ds_return_url' => $this->urlBuilder->getUrl('pagbank/ajax/vindithreedsreturn'),
                    'vindi_mpi_origin' => $detector->isSandbox()
                        ? 'https://sandbox-mpi.vindi.com.br'
                        : 'https://mpi.vindi.com.br',
                ],
                ConfigCc::METHOD_CODE => [
                    ConfigCc::CARD_BRAND_ICONS => $this->getIcons(),
                    ConfigCc::CC_VAULT_CODE => ConfigCcVault::METHOD_CODE,
                    ConfigCc::CC_THREED_SECURE => $threeDs,
                    ConfigCc::CC_THREED_SECURE_ALLOW_CONTINUE => $isVindi
                        ? false
                        : $this->configCc->isThreeDSecureAllowContinue($storeId),
                    ThreeDSecureSessionInterface::CONNECT_ENVIRONMENT => $this->config->isSandbox($storeId) ? 'SANDBOX' : 'PROD'
                ],
                ConfigCcVault::METHOD_CODE => [
                    ConfigCc::CARD_BRAND_ICONS => $this->getIcons()
                ],
                ConfigBoleto::METHOD_CODE => [
                    ConfigBoleto::CONFIG_EXPIRATION => $this->configBoleto->getExpirationTime()
                ],
                ConfigQrCode::METHOD_CODE => [
                    ConfigQrCode::CONFIG_EXPIRATION => $this->configQrCode->getQrCodeExpiration()
                ],
            ]
        ];
    }

    /**
     * Retrieve credit card icons
     *
     * @return array
     */
    private function getIcons()
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $types = $this->configCc->getCcAvailableTypes();
        if (empty($types)) {
            return $this->icons;
        }

        foreach ($types as $code => $label) {
            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->configCc->createAsset('RicardoMartins_PagBank::images/cc/' . strtolower($code) . '.svg');
                $placeholder = $this->configCc->findSource($asset);
                if ($placeholder) {
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => 40,
                        'height' => 25,
                        'title' => __($label),
                    ];
                }
            }
        }

        return $this->icons;
    }
}
