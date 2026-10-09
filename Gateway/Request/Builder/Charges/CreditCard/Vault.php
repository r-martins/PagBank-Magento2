<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder\Charges\CreditCard;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\AmountInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\ChargeInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterfaceFactory;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;

class Vault implements BuilderInterface
{
    /**
     * Represents all data available on a charge.
     * Receives an array of charges.
     */
    public const CHARGES = 'charges';

    public function __construct(
        private readonly ChargeInterfaceFactory $chargeFactory,
        private readonly AmountInterfaceFactory $amountFactory,
        private readonly CardInterfaceFactory $cardFactory,
        private readonly PaymentMethodInterfaceFactory $paymentMethodFactory,
        private readonly ConfigCc $config,
        private readonly Config $gatewayConfig
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $order = $paymentDataObject->getOrder();

        /** @var Order $orderModel */
        $orderModel = $payment->getOrder();

        $result = [];

        $charges = $this->chargeFactory->create();
        $charges->setReferenceId($orderModel->getIncrementId());

        $amount = $this->amountFactory->create();
        $amount->setValue($order->getGrandTotalAmount());
        $amount->setCurrency($order->getCurrencyCode());

        $charges->setAmount($amount->getData());

        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        $gatewayToken = (string) $paymentToken->getGatewayToken();
        $isVindi = $this->gatewayConfig->isVindi($orderModel->getStoreId());

        $card = $this->cardFactory->create();
        $paymentMethod = $this->paymentMethodFactory->create();
        $paymentMethod->setType(PaymentMethodInterface::TYPE_CREDIT_CARD);
        $paymentMethod->setInstallments((int) $payment->getAdditionalInformation('cc_installments'));
        $paymentMethod->setCapture(true);

        if ($isVindi && ctype_digit($gatewayToken)) {
            $profileId = (int) $gatewayToken;
            $card->setPaymentProfileId($profileId);
            $paymentMethod->setPaymentProfileId($profileId);
        } elseif ($isVindi) {
            $card->setGatewayToken($gatewayToken);
        } else {
            $card->setCardId($gatewayToken);
            $paymentMethod->setSoftDescriptor($this->config->getSoftDescriptor($orderModel->getStoreId()));
        }

        $paymentMethod->setCard($card->getData());

        $charges->setPaymentMethod($paymentMethod->getData());

        $result[self::CHARGES][] = $charges->getData();

        return $result;
    }
}
