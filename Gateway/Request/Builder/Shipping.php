<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\AddressInterfaceFactory;

class Shipping implements BuilderInterface
{
    /**
     * Shipping order information
     */
    public const SHIPPING = 'shipping';

    /**
     * Shipping address information
     */
    public const SHIPPING_ADDRESS = 'address';

    /**
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        private AddressInterfaceFactory $addressFactory
    ) {}

    /**
     * @param array $buildSubject
     * @return array
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

        if ($orderModel->getIsVirtual()) {
            return $result;
        }

        $shippingAddress = $order->getShippingAddress();
        $streetAddress = $shippingAddress->getStreet();

        $address = $this->addressFactory->create();
        $address->setStreet($streetAddress[0]);
        $address->setNumber($streetAddress[1]);
        $address->setLocality($streetAddress[3]);
        $address->setCity($shippingAddress->getCity());
        $address->setRegion($shippingAddress->getRegionCode(), $shippingAddress->getCountryId());
        $address->setRegionCode($shippingAddress->getRegionCode());
        $address->setPostalCode($shippingAddress->getPostcode());
        $address->setCountry();

        if (isset($streetAddress[2]) && !empty($streetAddress[2])) {
            $address->setComplement($streetAddress[2]);
        }

        $result[self::SHIPPING][self::SHIPPING_ADDRESS] = $address->getData();

        return $result;
    }
}
