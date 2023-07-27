<?php

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\ItemInterface;

class Item extends DataObject implements ItemInterface
{
    /**
     * @return string
     */
    public function getReferenceId(): string
    {
        return $this->getData(ItemInterface::REFERENCE_ID);
    }

    /**
     * @param string $referenceId
     * @return ItemInterface
     */
    public function setReferenceId(string $referenceId): ItemInterface
    {
        $referenceId = substr($referenceId, 0, 255);
        return $this->setData(ItemInterface::REFERENCE_ID, $referenceId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(ItemInterface::NAME);
    }

    /**
     * @param string $name
     * @return ItemInterface
     */
    public function setName(string $name): ItemInterface
    {
        $name = substr($name, 0, 64);
        return $this->setData(ItemInterface::NAME, $name);
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->getData(ItemInterface::QUANTITY);
    }

    /**
     * @param int $quantity
     * @return ItemInterface
     */
    public function setQuantity(int $quantity): ItemInterface
    {
        return $this->setData(ItemInterface::QUANTITY, $quantity);
    }

    /**
     * @return int
     */
    public function getUnitAmount(): int
    {
        return $this->getData(ItemInterface::UNIT_AMOUNT);
    }

    /**
     * @param int|float $unitAmount
     * @return ItemInterface
     */
    public function setUnitAmount(int|float $unitAmount): ItemInterface
    {
        $unitAmount = $this->convertAmountToCents($unitAmount);
        return $this->setData(ItemInterface::UNIT_AMOUNT, $unitAmount);
    }

    /**
     * @param int|float $amount
     * @return int
     */
    private function convertAmountToCents(int|float $amount): int
    {
        return round($amount * 100);
    }
}
