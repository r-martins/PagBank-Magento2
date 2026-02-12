<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\PaymentMethod;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\AuthenticationMethodInterface;

class AuthenticationMethod extends DataObject implements AuthenticationMethodInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return (string)($this->getData(AuthenticationMethodInterface::TYPE) ?? '');
    }

    /**
     * @param string $type
     * @return AuthenticationMethodInterface
     */
    public function setType(string $type): AuthenticationMethodInterface
    {
        return $this->setData(AuthenticationMethodInterface::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)($this->getData(AuthenticationMethodInterface::ID) ?? '');
    }

    /**
     * @param string $id
     * @return AuthenticationMethodInterface
     */
    public function setId(string $id): AuthenticationMethodInterface
    {
        return $this->setData(AuthenticationMethodInterface::ID, $id);
    }
}
