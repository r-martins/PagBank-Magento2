<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use RicardoMartins\PagBank\Api\Connect\PublicKeyInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly Config $config,
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
                ConfigBoleto::METHOD_CODE => [
                    ConfigBoleto::CONFIG_EXPIRATION => $this->configBoleto->getExpirationTime()
                ],
                ConfigQrCode::METHOD_CODE => [
                    ConfigQrCode::CONFIG_EXPIRATION => $this->configQrCode->getQrCodeExpiration()
                ],
            ]
        ];
    }
}
