<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\BoletoInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface;

class PaymentMethod extends DataObject implements PaymentMethodInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getData(PaymentMethodInterface::TYPE);
    }

    /**
     * @param string $type
     * @return PaymentMethodInterface
     */
    public function setType(string $type): PaymentMethodInterface
    {
        return $this->setData(PaymentMethodInterface::TYPE, $type);
    }

    /**
     * @return ?int
     */
    public function getInstallments(): ?int
    {
        return $this->getData(PaymentMethodInterface::INSTALLMENTS);
    }

    /**
     * @param int|null $installments
     * @return PaymentMethodInterface
     */
    public function setInstallments(?int $installments): PaymentMethodInterface
    {
        return $this->setData(PaymentMethodInterface::INSTALLMENTS, $installments);
    }

    /**
     * @return ?string
     */
    public function getSoftDescriptor(): ?string
    {
        return $this->getData(PaymentMethodInterface::SOFT_DESCRIPTOR);
    }

    /**
     * @param string $softDescriptor
     * @return PaymentMethodInterface
     */
    public function setSoftDescriptor(string $softDescriptor): PaymentMethodInterface
    {
        $softDescriptor = substr($softDescriptor, 0, 17);
        return $this->setData(PaymentMethodInterface::SOFT_DESCRIPTOR, $softDescriptor);
    }

    /**
     * @return bool|null
     */
    public function getCapture(): ?bool
    {
        return $this->getData(PaymentMethodInterface::CAPTURE);
    }

    /**
     * @param bool|null $capture
     * @return PaymentMethod
     */
    public function setCapture(?bool $capture): PaymentMethodInterface
    {
        return $this->setData(PaymentMethodInterface::CAPTURE, $capture);
    }

    /**
     * @return CardInterface[]
     */
    public function getCard(): array
    {
        return $this->getData(PaymentMethodInterface::TYPE_CREDIT_CARD_OBJECT);
    }

    /**
     * @param CardInterface[] $card
     * @return PaymentMethodInterface
     */
    public function setCard(array $card): PaymentMethodInterface
    {
        return $this->setData(PaymentMethodInterface::TYPE_CREDIT_CARD_OBJECT, $card);
    }

    /**
     * @return BoletoInterface[]
     */
    public function getBoleto(): array
    {
        return $this->getData(PaymentMethodInterface::TYPE_BOLETO_OBJECT);
    }

    /**
     * @param BoletoInterface[] $boleto
     * @return PaymentMethodInterface
     */
    public function setBoleto(array $boleto): PaymentMethodInterface
    {
        return $this->setData(PaymentMethodInterface::TYPE_BOLETO_OBJECT, $boleto);
    }
}
