<?php

namespace RicardoMartins\PagBank\Api\Gateway\Data;

use Magento\Payment\Gateway\Data\AddressAdapterInterface as MagentoAddressAdapterInterface;

interface AddressAdapterInterface extends MagentoAddressAdapterInterface
{
    /**
     * Gets the street values
     *
     * @return array
     */
    public function getStreet(): array;

    /**
     * Get street line by number
     *
     * @param int $number
     * @return string
     */
    public function getStreetLine(int $number): string;
}
