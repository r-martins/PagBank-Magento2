<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\CustomerInterface;
use RicardoMartins\PagBank\Api\Connect\PhoneInterface;

class Customer extends DataObject implements CustomerInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(CustomerInterface::NAME);
    }

    /**
     * @param string $name
     * @return CustomerInterface
     */
    public function setName(string $name): CustomerInterface
    {
        $name = substr($name, 0, 30);
        return $this->setData(CustomerInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->getData(CustomerInterface::TAX_ID);
    }

    /**
     * @param string $taxId
     * @return CustomerInterface
     */
    public function setTaxId(string $taxId): CustomerInterface
    {
        return $this->setData(CustomerInterface::TAX_ID, $taxId);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getData(CustomerInterface::EMAIL);
    }

    /**
     * @param string $email
     * @return CustomerInterface
     */
    public function setEmail(string $email): CustomerInterface
    {
        $email = substr($email, 0, 255);
        return $this->setData(CustomerInterface::EMAIL, $email);
    }

    /**
     * @return PhoneInterface[]
     */
    public function getPhones(): array
    {
        return $this->getData(CustomerInterface::PHONES);
    }

    /**
     * @param PhoneInterface[] $phones
     * @return CustomerInterface
     */
    public function setPhones(array $phones): CustomerInterface
    {
        return $this->setData(CustomerInterface::PHONES, $phones);
    }
}
