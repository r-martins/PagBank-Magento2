<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use RicardoMartins\PagBank\Api\Connect\ChargeInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface;
use RicardoMartins\PagBank\Api\Connect\AmountInterface;

class Charge extends DataObject implements ChargeInterface
{
    /**
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        private readonly TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getReferenceId(): string
    {
        return $this->getData(ChargeInterface::REFERENCE_ID);
    }

    /**
     * @param string $referenceId
     * @return ChargeInterface
     */
    public function setReferenceId(string $referenceId): ChargeInterface
    {
        $referenceId = substr($referenceId, 0, 64);
        return $this->setData(ChargeInterface::REFERENCE_ID, $referenceId);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(ChargeInterface::CREATED_AT);
    }

    /**
     * @param null $date
     * @return ChargeInterface
     */
    public function setCreatedAt($date = null): ChargeInterface
    {
        $createdAt = $this->timezone->date($date)->format(self::DATETIME_FORMAT);
        return $this->setData(ChargeInterface::CREATED_AT, $createdAt);
    }

    /**
     * @return AmountInterface
     */
    public function getAmount(): array
    {
        return $this->getData(ChargeInterface::AMOUNT);
    }

    /**
     * @param AmountInterface[] $amount
     * @return ChargeInterface
     */
    public function setAmount(array $amount): ChargeInterface
    {
        return $this->setData(ChargeInterface::AMOUNT, $amount);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    public function getPaymentMethod(): array
    {
        return $this->getData(ChargeInterface::PAYMENT_METHOD);
    }

    /**
     * @param PaymentMethodInterface[] $paymentMethod
     * @return ChargeInterface
     */
    public function setPaymentMethod(array $paymentMethod): ChargeInterface
    {
        return $this->setData(ChargeInterface::PAYMENT_METHOD, $paymentMethod);
    }
}
