<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\Customer;

use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region;
use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\AddressInterface;

class Address extends DataObject implements AddressInterface
{
    /**
     * @param RegionFactory $regionFactory
     * @param Region $regionResource
     * @param array $data
     */
    public function __construct(
        private readonly RegionFactory $regionFactory,
        private readonly Region $regionResource,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->getData(AddressInterface::STREET);
    }

    /**
     * @param string $street
     * @return Address
     */
    public function setStreet(string $street): Address
    {
        $street = substr($street, 0, 160);
        return $this->setData(AddressInterface::STREET, $street);
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->getData(AddressInterface::NUMBER);
    }

    /**
     * @param string $number
     * @return Address
     */
    public function setNumber(string $number): Address
    {
        $number = substr($number, 0, 20);
        return $this->setData(AddressInterface::NUMBER, $number);
    }

    /**
     * @return string|null
     */
    public function getComplement(): ?string
    {
        return $this->getData(AddressInterface::COMPLEMENT);
    }

    /**
     * @param string $complement
     * @return Address
     */
    public function setComplement(string $complement): Address
    {
        $complement = substr($complement, 0, 40);
        return $this->setData(AddressInterface::COMPLEMENT, $complement);
    }

    /**
     * @return string
     */
    public function getLocality(): string
    {
        return $this->getData(AddressInterface::LOCALITY);
    }

    /**
     * @param string $locality
     * @return Address
     */
    public function setLocality(string $locality): Address
    {
        $locality = substr($locality, 0, 60);
        return $this->setData(AddressInterface::LOCALITY, $locality);
    }


    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->getData(AddressInterface::CITY);
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity(string $city): Address
    {
        $city = substr($city, 0, 90);
        return $this->setData(AddressInterface::CITY, $city);
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->getData(AddressInterface::REGION);
    }

    /**
     * @param string $regionCode
     * @param string $countryId
     * @return Address
     */
    public function setRegion(string $regionCode, string $countryId): Address
    {
        $region = $this->getRegionName($regionCode, $countryId);
        $region = substr($region, 0, 50);
        return $this->setData(AddressInterface::REGION, $region);
    }

    /**
     * @return string
     */
    public function getRegionCode(): string
    {
        return $this->getData(AddressInterface::REGION_CODE);
    }

    /**
     * @param string $regionCode
     * @return Address
     */
    public function setRegionCode(string $regionCode): Address
    {
        $regionCode = substr($regionCode, 0, 2);
        return $this->setData(AddressInterface::REGION_CODE, $regionCode);
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->getData(AddressInterface::COUNTRY);
    }

    /**
     * @param string|null $country
     * @return Address
     */
    public function setCountry(string $country = null): Address
    {
        $country = $country ?? AddressInterface::COUNTRY_CODE_BRAZIL;
        $country = substr($country, 0, 3);
        return $this->setData(AddressInterface::COUNTRY, $country);
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->getData(AddressInterface::POSTAL_CODE);
    }

    /**
     * @param string $postalCode
     * @return Address
     */
    public function setPostalCode(string $postalCode): Address
    {
        $postalCode = substr($postalCode, 0, 8);
        return $this->setData(AddressInterface::POSTAL_CODE, $postalCode);
    }

    /**
     * @param string $regionCode
     * @param string $countryId
     * @return string
     */
    private function getRegionName(string $regionCode, string $countryId): string
    {
        $region = $this->regionFactory->create();
        $this->regionResource->loadByCode($region, $regionCode, $countryId);
        return $region->getName();
    }
}
