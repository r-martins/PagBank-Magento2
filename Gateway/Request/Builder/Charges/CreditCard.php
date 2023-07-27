<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder\Charges;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\ChargeInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\HolderInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterfaceFactory;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Model\Request\ChargeFactory;
use RicardoMartins\PagBank\Api\Connect\AmountInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterfaceFactory;

class CreditCard implements BuilderInterface
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
        private readonly HolderInterfaceFactory $holderFactory,
        private readonly PaymentMethodInterfaceFactory $paymentMethodFactory,
        private readonly ConfigCc $config
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

        $holder = $this->holderFactory->create();
        $holder->setName($payment->getData('cc_owner'));

        $card = $this->cardFactory->create();
        $card->setHolder($holder->getData());
        $card->setEncrypted($payment->getAdditionalInformation('cc_number_encrypted'));

        $paymentMethod = $this->paymentMethodFactory->create();
        $paymentMethod->setType(PaymentMethodInterface::TYPE_CREDIT_CARD);
        $paymentMethod->setInstallments((int) $payment->getAdditionalInformation('cc_installments'));
        $paymentMethod->setCapture(true);
        $paymentMethod->setCard($card->getData());

        $softDescriptor = $this->config->getSoftDescriptor($orderModel->getStoreId());
        $paymentMethod->setSoftDescriptor($softDescriptor);

        $charges->setPaymentMethod($paymentMethod->getData());

        $result[self::CHARGES][] = $charges->getData();

        return $result;
    }
}
