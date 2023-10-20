<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Data\Order;

use RicardoMartins\PagBank\Api\Gateway\Data\AddressAdapterInterface as PagbankAddressAdapterInterface;
use Magento\Payment\Gateway\Data\Order\AddressAdapter as MagentoAddressAdapter;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class AddressAdapter
 * Extends Magento's payment AddressAdapter to provide possibility get all street addresses.
 */
class AddressAdapter extends MagentoAddressAdapter implements PagbankAddressAdapterInterface
{
    /**
     * @param OrderAddressInterface $address
     */
    public function __construct(
        private readonly OrderAddressInterface $address
    ) {
        parent::__construct($address);
    }

    /**
     * @inheritdoc
     */
    public function getStreet(): array
    {
        $street = (array) $this->address->getStreet();
        return empty($street) ? [] : $street;
    }

    /**
     * @inheritdoc
     */
    public function getStreetLine($number): string
    {
        $lines = $this->getStreet();
        return $lines[$number - 1] ?? '';
    }
}
