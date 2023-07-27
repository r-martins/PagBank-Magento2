<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\Customer;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\PhoneInterface;

class Phone extends DataObject implements PhoneInterface
{
    /**
     * @return int
     */
    public function getCountry(): int
    {
        return $this->getData(PhoneInterface::COUNTRY);
    }

    /**
     * @param int $country
     * @return Phone
     */
    public function setCountry(int $country): Phone
    {
        return $this->setData(PhoneInterface::COUNTRY, $country);
    }

    /**
     * @return int
     */
    public function getArea(): int
    {
        return $this->getData(PhoneInterface::AREA);
    }

    /**
     * @param int $area
     * @return Phone
     */
    public function setArea(int $area): Phone
    {
        return $this->setData(PhoneInterface::AREA, $area);
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->getData(PhoneInterface::NUMBER);
    }

    /**
     * @param int $number
     * @return Phone
     */
    public function setNumber(int $number): Phone
    {
        return $this->setData(PhoneInterface::NUMBER, $number);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getData(PhoneInterface::TYPE);
    }

    /**
     * @param string $type
     * @return Phone
     */
    public function setType(string $type): Phone
    {
        return $this->setData(PhoneInterface::TYPE, $type);
    }
}
