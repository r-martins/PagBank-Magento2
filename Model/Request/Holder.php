<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\HolderInterface;
use RicardoMartins\PagBank\Api\Connect\AddressInterface;

class Holder extends DataObject implements HolderInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(HolderInterface::NAME);
    }

    /**
     * @param string $name
     * @return HolderInterface
     */
    public function setName(string $name): HolderInterface
    {
        $name = substr($name, 0, 30);
        return $this->setData(HolderInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->getData(HolderInterface::TAX_ID);
    }

    /**
     * @param string $taxId
     * @return HolderInterface
     */
    public function setTaxId(string $taxId): HolderInterface
    {
        $taxId = preg_replace('/\D/', '', $taxId);
        return $this->setData(HolderInterface::TAX_ID, $taxId);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getData(HolderInterface::EMAIL);
    }

    /**
     * @param string $email
     * @return HolderInterface
     */
    public function setEmail(string $email): HolderInterface
    {
        $email = strtolower($email);
        $email = substr($email, 0, 255);
        return $this->setData(HolderInterface::EMAIL, $email);
    }

    /**
     * @return AddressInterface[]
     */
    public function getAddress(): array
    {
        return $this->getData(HolderInterface::ADDRESS);
    }

    /**
     * @param AddressInterface[] $address
     * @return HolderInterface
     */
    public function setAddress(array $address): HolderInterface
    {
        return $this->setData(HolderInterface::ADDRESS, $address);
    }
}
