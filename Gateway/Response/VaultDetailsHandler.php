<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Response;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;

class VaultDetailsHandler implements HandlerInterface
{
    public function __construct(
        private readonly PaymentTokenFactoryInterface $paymentTokenFactory,
        private readonly OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        private readonly SerializerInterface $serializer,
        private readonly ConfigCc $configCc
    ) {}

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $handlingSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($response);
        if ($paymentToken !== null) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }

    }

    /**
     * Get vault payment token entity
     *
     * @param array $response
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken(array $response)
    {
        // Check card id existing in gateway response
        $cardData = $response['charges'][0]['payment_method']['card'];
        if (empty($cardData) || !isset($cardData['id'])) {
            return null;
        }

        $cardId = $cardData['id'];

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($cardId);
        $paymentToken->setExpiresAt(strtotime('+1 year'));
        $paymentToken->setIsVisible(true);

        $details = [
            'cc_bin'       => $cardData['first_digits'],
            'cc_last4'     => $cardData['last_digits'],
            'cc_exp_year'  => $cardData['exp_year'],
            'cc_exp_month' => $cardData['exp_month'],
            'cc_type'      => $this->getCcType($cardData['brand']),
        ];
        $paymentToken->setTokenDetails($this->serializer->serialize($details));

        return $paymentToken;
    }

    /**
     * Get payment extension attributes.
     *
     * @param InfoInterface $payment     *
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * Convert card brand to Magento format.
     * @param string $brand
     * @return string
     */
    private function getCcType(string $brand)
    {
        $brand = strtoupper($brand);
        $mappedCcTypes = array_flip($this->configCc->getCcAvailableTypes());

        return $mappedCcTypes[$brand] ?? $brand;
    }
}
