<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\CustomerInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PhoneInterface;
use RicardoMartins\PagBank\Api\Connect\PhoneInterfaceFactory;

class Customer implements BuilderInterface
{
    /**
     * Customer information
     */
    public const CUSTOMER = 'customer';

    /**
     * @param CustomerInterfaceFactory $customerFactory
     * @param PhoneInterfaceFactory $phoneFactory
     */
    public function __construct(
        private CustomerInterfaceFactory $customerFactory,
        private PhoneInterfaceFactory $phoneFactory
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        /** @var Order $orderModel */
        $orderModel = $payment->getOrder();
        $telephone = $orderModel->getBillingAddress()->getTelephone();

        $phones = $this->phoneFactory->create();
        $phones->setCountry(PhoneInterface::DEFAULT_COUNTRY_CODE);
        $phones->setArea((int) substr($telephone, 0, 2));
        $phones->setNumber((int) substr($telephone, 2));
        $phones->setType($this->getPhoneType($telephone, $orderModel->getCustomerTaxvat()));

        $customer = $this->customerFactory->create();
        $customer->setName($orderModel->getCustomerFirstname() . ' ' . $orderModel->getCustomerLastname());
        $customer->setTaxId($orderModel->getCustomerTaxvat());
        $customer->setEmail($orderModel->getCustomerEmail());
        $customer->setPhones([$phones->getData()]);

        return [
            self::CUSTOMER => $customer->getData()
        ];
    }

    /**
     * @param string $telephone
     * @param string $taxvat
     * @return string
     */
    private function getPhoneType(string $telephone, string $taxvat): string
    {
        $countTaxvatCharacters = strlen($taxvat);
        if ($countTaxvatCharacters === 14) {
            return PhoneInterface::TYPE_BUSINESS;
        }

        $countPhoneCharacters = strlen($telephone);
        if ($countPhoneCharacters === 8) {
            return PhoneInterface::TYPE_HOME;
        }

        return PhoneInterface::TYPE_MOBILE;
    }
}
