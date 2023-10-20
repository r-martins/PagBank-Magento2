<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Data\Order;

use RicardoMartins\PagBank\Api\Gateway\Data\AddressAdapterInterface as PagbankAddressAdapterInterface ;
use RicardoMartins\PagBank\Api\Gateway\Data\AddressAdapterInterfaceFactory as PagbankAddressAdapterFactory;
use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\Data\Order\OrderAdapter as MagentoOrderAdapter;

class OrderAdapter extends MagentoOrderAdapter implements OrderAdapterInterface
{
    public function __construct(
        private readonly Order $order,
        private readonly PagbankAddressAdapterFactory $pagbankAddressAdapterFactory,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        parent::__construct($order, $addressAdapterFactory);
    }

    /**
     * Returns billing address
     *
     * @return PagbankAddressAdapterInterface|null
     */
    public function getBillingAddress(): ?PagbankAddressAdapterInterface
    {
        if ($this->order->getBillingAddress()) {
            return $this->pagbankAddressAdapterFactory->create(
                ['address' => $this->order->getBillingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns shipping address
     *
     * @return PagbankAddressAdapterInterface|null
     */
    public function getShippingAddress(): ?PagbankAddressAdapterInterface
    {
        if ($this->order->getShippingAddress()) {
            return $this->pagbankAddressAdapterFactory->create(
                ['address' => $this->order->getShippingAddress()]
            );
        }

        return null;
    }
}
