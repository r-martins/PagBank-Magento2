<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Ui;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use RicardoMartins\PagBank\Gateway\Config\ConfigCcVault;

class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    public function __construct(
        private readonly TokenUiComponentInterfaceFactory $componentFactory,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * @inheritDoc
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = $this->serializer->unserialize($paymentToken->getTokenDetails());
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigCcVault::METHOD_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'RicardoMartins_PagBank/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}
