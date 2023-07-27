<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\AmountInterface;

class Amount extends DataObject implements AmountInterface
{
    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->getData(AmountInterface::VALUE);
    }

    /**
     * @param int|float $value
     * @return AmountInterface
     */
    public function setValue(int|float $value): AmountInterface
    {
        return $this->setData(AmountInterface::VALUE, $this->convertAmountToCents($value));
    }

    /**
     * @return ?string
     */
    public function getCurrency(): ?string
    {
        return $this->getData(AmountInterface::CURRENCY);
    }

    /**
     * @param string|null $currency
     * @return AmountInterface
     */
    public function setCurrency(?string $currency): AmountInterface
    {
        return $this->setData(AmountInterface::CURRENCY, $currency);
    }

    /**
     * @param $amount
     * @return int
     */
    private function convertAmountToCents($amount): int
    {
        return (int) round($amount * 100);
    }
}
