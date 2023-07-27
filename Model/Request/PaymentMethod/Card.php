<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\PaymentMethod;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterface;

class Card extends DataObject implements CardInterface
{
    /**
     * @return string
     */
    public function getEncrypted(): string
    {
        return $this->getData(CardInterface::ENCRYPTED);
    }

    /**
     * @param string $encrypted
     * @return CardInterface
     */
    public function setEncrypted(string $encrypted): CardInterface
    {
        return $this->setData(CardInterface::ENCRYPTED, $encrypted);
    }

    /**
     * @return string
     */
    public function getSecurityCode(): string
    {
        return $this->getData(CardInterface::SECURITY_CODE);
    }

    /**
     * @param int $securityCode
     * @return CardInterface
     */
    public function setSecurityCode(int $securityCode): CardInterface
    {
        return $this->setData(CardInterface::SECURITY_CODE, $securityCode);
    }

    /**
     * @return array
     */
    public function getHolder(): array
    {
        return $this->getData(CardInterface::HOLDER);
    }

    /**
     * @param array $holder
     * @return CardInterface
     */
    public function setHolder(array $holder): CardInterface
    {
        return $this->setData(CardInterface::HOLDER, $holder);
    }
}
