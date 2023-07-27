<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\InstallmentsInterface;

class Installments extends DataObject implements InstallmentsInterface
{
    /**
     * @return array|null
     */
    public function getPaymentMethods(): ?array
    {
        return $this->getData(InstallmentsInterface::PAYMENT_METHODS);
    }

    /**
     * @param string $paymentMethods
     * @return InstallmentsInterface
     */
    public function setPaymentMethods(string $paymentMethods): InstallmentsInterface
    {
        return $this->setData(InstallmentsInterface::PAYMENT_METHODS, $paymentMethods);
    }

    /**
     * @return ?int
     */
    public function getCreditCardBin(): ?int
    {
        return $this->getData(InstallmentsInterface::CREDIT_CARD_BIN);
    }

    /**
     * @param ?int $creditCardBin
     * @return InstallmentsInterface
     */
    public function setCreditCardBin(?int $creditCardBin): InstallmentsInterface
    {
        return $this->setData(InstallmentsInterface::CREDIT_CARD_BIN, $creditCardBin);
    }

    /**
     * @return int|null
     */
    public function getMaxInstallments(): ?int
    {
        return $this->getData(InstallmentsInterface::MAX_INSTALLMENTS);
    }

    /**
     * @param int|null $maxInstallments
     * @return InstallmentsInterface
     */
    public function setMaxInstallments(?int $maxInstallments): InstallmentsInterface
    {
        return $this->setData(InstallmentsInterface::MAX_INSTALLMENTS, $maxInstallments);
    }

    /**
     * @return int|null
     */
    public function getMaxInstallmentsNoInterest(): ?int
    {
        return $this->getData(InstallmentsInterface::MAX_INSTALLMENTS_NO_INTEREST);
    }

    /**
     * @param int|null $maxInstallmentsNoInterest
     * @return InstallmentsInterface
     */
    public function setMaxInstallmentsNoInterest(?int $maxInstallmentsNoInterest): InstallmentsInterface
    {
        return $this->setData(InstallmentsInterface::MAX_INSTALLMENTS_NO_INTEREST, $maxInstallmentsNoInterest);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->getData(InstallmentsInterface::VALUE);
    }

    /**
     * @param float|int $value
     * @return InstallmentsInterface
     */
    public function setValue(float|int $value): InstallmentsInterface
    {
        return $this->setData(InstallmentsInterface::VALUE, $this->convertAmountToCents($value));
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
