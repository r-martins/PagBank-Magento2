<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\PaymentMethod;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use RicardoMartins\PagBank\Api\Connect\AmountInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\QrCodeInterface;

class QrCode extends DataObject implements QrCodeInterface
{
    public function __construct(
        private readonly TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return AmountInterface[]
     */
    public function getAmount(): array
    {
        return $this->getData(QrCodeInterface::AMOUNT);
    }

    /**
     * @param AmountInterface[] $amount
     * @return QrCodeInterface
     */
    public function setAmount(array $amount): QrCodeInterface
    {
        return $this->setData(QrCodeInterface::AMOUNT, $amount);
    }

    /**
     * @return string
     */
    public function getExpirationDate(): string
    {
        return $this->getData(QrCodeInterface::EXPIRATION_DATE);
    }

    /**
     * @param string $modifier
     * @return QrCodeInterface
     */
    public function setExpirationDate(string $modifier = '60'): QrCodeInterface
    {
        $modifier = sprintf('+%s minutes', $modifier);
        $expirationDate = $this->timezone->date()->modify($modifier);
        return $this->setData(QrCodeInterface::EXPIRATION_DATE, $expirationDate->format(self::DATETIME_FORMAT));
    }
}
