<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use RicardoMartins\PagBank\Api\Connect\ThreeDSecureSessionInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Config\ConfigCcVault;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;

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
        private readonly StoreManagerInterface $storeManager
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
        $publicKey = $this->config->getPublicKey($storeId);

        if (empty($connectKey) || empty($publicKey)) {
            return [];
        }

        return [
            'payment' => [
                Config::METHOD_CODE => [
                    Config::CONFIG_PUBLIC_KEY => $this->config->getPublicKey($storeId),
                    Config::CONFIG_DOCUMENT_FROM => $this->config->getDocumentFrom($storeId),
                ],
                ConfigCc::METHOD_CODE => [
                    ConfigCc::CARD_BRAND_ICONS => $this->getIcons(),
                    ConfigCc::CC_VAULT_CODE => ConfigCcVault::METHOD_CODE,
                    ConfigCc::CC_THREED_SECURE => $this->configCc->isThreeDSecureActive($storeId),
                    ConfigCc::CC_THREED_SECURE_ALLOW_CONTINUE => $this->configCc->isThreeDSecureAllowContinue($storeId),
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
