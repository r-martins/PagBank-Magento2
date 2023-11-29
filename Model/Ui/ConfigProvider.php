<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
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
        private readonly ConfigQrCode $configQrCode
    ) {}

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                Config::METHOD_CODE => [
                    Config::CONFIG_PUBLIC_KEY => $this->config->getPublicKey(),
                    Config::CONFIG_DOCUMENT_FROM => $this->config->getDocumentFrom(),
                ],
                ConfigCc::METHOD_CODE => [
                    ConfigCc::CARD_BRAND_ICONS => $this->getIcons(),
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
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height,
                        'title' => __($label),
                    ];
                }
            }
        }

        return $this->icons;
    }
}
