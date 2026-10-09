<?php

namespace RicardoMartins\PagBank\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;

class DataAssignObserver extends AbstractDataAssignObserver
{
    public const CUSTOMER_TAX_ID = 'tax_id';

    public function __construct(private readonly Config $config)
    {
    }

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

        $storeId = $paymentInfo->getOrder() ? $paymentInfo->getOrder()->getStoreId() : null;
        $detector = $this->config->getPartnerDetector($storeId);
        $paymentInfo->setAdditionalInformation('partner', $detector->getPartner());
        $paymentInfo->setAdditionalInformation('connect_key_fp', $detector->fingerprint());
    }
}
