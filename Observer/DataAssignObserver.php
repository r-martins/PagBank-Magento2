<?php

namespace RicardoMartins\PagBank\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{
    public const CUSTOMER_TAX_ID = 'tax_id';

    private array $paymentAdditionalFields = [
        self::CUSTOMER_TAX_ID
    ];

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->paymentAdditionalFields as $field) {
            if (isset($additionalData[$field])) {
                $paymentInfo->setAdditionalInformation(
                    $field,
                    $additionalData[$field]
                );
            }
        }
    }
}
