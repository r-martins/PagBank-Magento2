<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\PaymentMethod;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterface;

class Card extends DataObject implements CardInterface
{
    /**
     * @return string|null
     */
    public function getEncrypted(): ?string
    {
        return $this->getData(CardInterface::ENCRYPTED);
    }

    /**
     * @param string|null $encrypted
     * @return CardInterface
     */
    public function setEncrypted(?string $encrypted): CardInterface
    {
        return $this->setData(CardInterface::ENCRYPTED, $encrypted);
    }

    /**
     * @return string|null
     */
    public function getCardId(): ?string
    {
        return $this->getData(CardInterface::CARD_ID);
    }

    /**
     * @param string|null $cardId
     * @return CardInterface
     */
    public function setCardId(?string $cardId): CardInterface
    {
        return $this->setData(CardInterface::CARD_ID, $cardId);
    }

    /**
     * @return string|null
     */
    public function getSecurityCode(): ?string
    {
        return $this->getData(CardInterface::SECURITY_CODE);
    }

    /**
     * @param int|null $securityCode
     * @return CardInterface
     */
    public function setSecurityCode(?int $securityCode): CardInterface
    {
        return $this->setData(CardInterface::SECURITY_CODE, $securityCode);
    }

    /**
     * @return array|null
     */
    public function getHolder(): ?array
    {
        return $this->getData(CardInterface::HOLDER);
    }

    /**
     * @param array|null $holder
     * @return CardInterface
     */
    public function setHolder(?array $holder): CardInterface
    {
        return $this->setData(CardInterface::HOLDER, $holder);
    }
}
