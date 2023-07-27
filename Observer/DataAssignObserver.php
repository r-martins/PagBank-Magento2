<?php

namespace RicardoMartins\PagBank\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{
    public const CC_NUMBER = 'cc_number';
    public const CC_TYPE_4 = 'cc_last_4';
    public const CC_TYPE = 'cc_type';
    public const CC_EXP_MONTH = 'cc_exp_month';
    public const CC_EXP_YEAR = 'cc_exp_year';
    public const CC_CID = 'cc_cid';
    public const CC_OWNER = 'cc_owner';
    public const CC_OWNER_DOCUMENT = 'cc_owner_document';
    public const CC_INSTALLMENTS = 'cc_installments';
    public const CC_NUMBER_ENCRYPTED = 'cc_number_encrypted';

    private array $paymentFields = [
        self::CC_TYPE,
        self::CC_CID,
        self::CC_OWNER,
        self::CC_EXP_MONTH,
        self::CC_EXP_YEAR,
        self::CC_TYPE_4,
    ];

    private array $paymentAdditionalFields = [
        self::CC_OWNER_DOCUMENT,
        self::CC_INSTALLMENTS,
        self::CC_NUMBER_ENCRYPTED
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

        foreach ($this->paymentFields as $field) {
            if (isset($additionalData[$field])) {
                $paymentInfo->setData(
                    $field,
                    $additionalData[$field]
                );
            }
        }

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
