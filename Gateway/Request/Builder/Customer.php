<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\CustomerInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PhoneInterface;
use RicardoMartins\PagBank\Api\Connect\PhoneInterfaceFactory;
use RicardoMartins\PagBank\Gateway\Config\Config;

class Customer implements BuilderInterface
{
    /**
     * Customer information
     */
    public const CUSTOMER = 'customer';

    /**
     * @param CustomerInterfaceFactory $customerFactory
     * @param PhoneInterfaceFactory $phoneFactory
     * @param Config $config
     */
    public function __construct(
        private CustomerInterfaceFactory $customerFactory,
        private PhoneInterfaceFactory $phoneFactory,
        private Config $config
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        /** @var Order $orderModel */
        $orderModel = $payment->getOrder();

        $documentFrom = $this->config->getDocumentFrom($orderModel->getStoreId());

        $document = match ($documentFrom) {
            'taxvat' => $orderModel->getCustomerTaxvat(),
            'vat_id' => $orderModel->getBillingAddress()->getVatId(),
            default => $payment->getAdditionalInformation('tax_id'),
        };

        if (!$document) {
            $document = $payment->getAdditionalInformation('tax_id');
        }

        $rawTelephone = $orderModel->getBillingAddress()->getTelephone();
        $digitsOnly = (string) preg_replace('/\D/', '', (string) $rawTelephone);
        $document = preg_replace('/\D/', '', (string) $document);

        $localDigits = $this->extractBrazilLocalDigits($digitsOnly);
        $areaAndNumber = $this->splitBrazilAreaAndNumber($localDigits);

        $phones = $this->phoneFactory->create();
        $phones->setCountry(PhoneInterface::DEFAULT_COUNTRY_CODE);
        $phones->setArea($areaAndNumber['area']);
        $phones->setNumber($areaAndNumber['number']);
        $phones->setType($this->getPhoneType($localDigits, $document));

        $customer = $this->customerFactory->create();
        $customer->setName($orderModel->getCustomerFirstname() . ' ' . $orderModel->getCustomerLastname());
        $customer->setTaxId($document);
        $customer->setEmail($orderModel->getCustomerEmail());
        $customer->setPhones([$phones->getData()]);

        return [
            self::CUSTOMER => $customer->getData()
        ];
    }

    /**
     * Strips long-distance 0 and DDI 55 when present so the remainder is national format (DDD + number).
     *
     * Brazilian mobiles are DDD (2) + 9 digits; landlines DDD (2) + 8 digits.
     * Leading 55 is only removed when what remains is exactly 10 or 11 digits, so DDD 55 (e.g. RS) is preserved.
     */
    private function extractBrazilLocalDigits(string $digitsOnly): string
    {
        if ($digitsOnly === '') {
            return '';
        }

        // 0 + DDD + number (national long distance)
        if (str_starts_with($digitsOnly, '0') && strlen($digitsOnly) >= 11) {
            $digitsOnly = substr($digitsOnly, 1);
        }

        while (
            strlen($digitsOnly) > 11
            && str_starts_with($digitsOnly, '55')
        ) {
            $rest = substr($digitsOnly, 2);
            if (strlen($rest) !== 10 && strlen($rest) !== 11) {
                break;
            }
            $digitsOnly = $rest;
        }

        return $digitsOnly;
    }

    /**
     * @return array{area: int, number: int}
     */
    private function splitBrazilAreaAndNumber(string $localDigits): array
    {
        $len = strlen($localDigits);
        if ($len === 11) {
            return [
                'area' => (int) substr($localDigits, 0, 2),
                'number' => (int) substr($localDigits, 2, 9),
            ];
        }
        if ($len === 10) {
            return [
                'area' => (int) substr($localDigits, 0, 2),
                'number' => (int) substr($localDigits, 2, 8),
            ];
        }

        // Unusual length: keep previous behavior (first two digits = area) to avoid empty payload
        if ($len >= 3) {
            return [
                'area' => (int) substr($localDigits, 0, 2),
                'number' => (int) substr($localDigits, 2),
            ];
        }

        return [
            'area' => $len > 0 ? (int) $localDigits : 0,
            'number' => 0,
        ];
    }

    /**
     * @param string $localDigits National digits only (DDD + subscriber), 10 or 11 chars when well-formed
     * @param string|null $taxvat
     * @return string
     */
    private function getPhoneType(string $localDigits, ?string $taxvat = null): string
    {
        if (!$taxvat) {
            return PhoneInterface::TYPE_MOBILE;
        }

        if (strlen($taxvat) === 14) {
            return PhoneInterface::TYPE_BUSINESS;
        }

        $subscriber = strlen($localDigits) >= 2 ? substr($localDigits, 2) : $localDigits;
        if (strlen($subscriber) === 8) {
            return PhoneInterface::TYPE_HOME;
        }

        return PhoneInterface::TYPE_MOBILE;
    }
}
