<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder\Charges;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\AddressInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\ChargeInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\CustomerInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\HolderInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\BoletoInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\InstructionLinesInterfaceFactory;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Api\Connect\AmountInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterfaceFactory;

class Boleto implements BuilderInterface
{
    /**
     * Represents all data available on a charge.
     * Receives an array of charges.
     */
    public const CHARGES = 'charges';

    public function __construct(
        private ChargeInterfaceFactory $chargeFactory,
        private BoletoInterfaceFactory $boletoFactory,
        private AmountInterfaceFactory $amountFactory,
        private PaymentMethodInterfaceFactory $paymentMethodFactory,
        private InstructionLinesInterfaceFactory $instructionLinesFactory,
        private HolderInterfaceFactory $holderFactory,
        private AddressInterfaceFactory $addressFactory,
        private \RicardoMartins\PagBank\Gateway\Config\ConfigBoleto $config
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
        $storeId = $orderModel->getStoreId();

        $billingAddress = $order->getBillingAddress();
        $streetAddress = $billingAddress->getStreet();

        $address = $this->addressFactory->create();
        $address->setStreet($streetAddress[0]);
        $address->setNumber($streetAddress[1]);
        $address->setComplement(isset($streetAddress[2]) ? $streetAddress[2] : null);
        $address->setLocality(isset($streetAddress[3]) ? $streetAddress[3] : null);
        $address->setCity($billingAddress->getCity());
        $address->setRegion($billingAddress->getRegionCode(), $billingAddress->getCountryId());
        $address->setRegionCode($billingAddress->getRegionCode());
        $address->setPostalCode($billingAddress->getPostcode());
        $address->setCountry();

        $holder = $this->holderFactory->create();
        $holder->setName($orderModel->getCustomerFirstname() . ' ' . $orderModel->getCustomerLastname());
        $holder->setTaxId($orderModel->getCustomerTaxvat());
        $holder->setEmail($orderModel->getCustomerEmail());
        $holder->setAddress($address->getData());

        $instructionLines = $this->instructionLinesFactory->create();
        $instructionLines->setLineOne($this->config->getInstructionLineOne($storeId));
        $instructionLines->setLineTwo($this->config->getInstructionLineTwo($storeId));

        $boleto = $this->boletoFactory->create();
        $expiration = $this->config->getExpirationTime($storeId) ?? 3;
        $boleto->setDueDate(date('Y-m-d', strtotime('+' . $expiration . 'day')));
        $boleto->setInstructionLines($instructionLines->getData());
        $boleto->setHolder($holder->getData());

        $paymentMethod = $this->paymentMethodFactory->create();
        $paymentMethod->setType(PaymentMethodInterface::TYPE_BOLETO);
        $paymentMethod->setBoleto($boleto->getData());

        $amount = $this->amountFactory->create();
        $amount->setValue($order->getGrandTotalAmount());
        $amount->setCurrency($order->getCurrencyCode());

        $charges = $this->chargeFactory->create();
        $charges->setCreatedAt();
        $charges->setReferenceId($orderModel->getIncrementId());
        $charges->setAmount($amount->getData());
        $charges->setPaymentMethod($paymentMethod->getData());

        $result[self::CHARGES][] = $charges->getData();

        return $result;
    }
}
