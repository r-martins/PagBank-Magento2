<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use RicardoMartins\PagBank\Api\Connect\PublicKeyInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly Config $config
    ) {}

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                Config::METHOD_CODE => [
                    PublicKeyInterface::PUBLIC_KEY => $this->config->getPublicKey()
                ]
            ]
        ];
    }
}
