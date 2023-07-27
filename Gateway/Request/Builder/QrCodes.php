<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;
use RicardoMartins\PagBank\Model\Request\PaymentMethod\QrCodeFactory;
use RicardoMartins\PagBank\Api\Connect\AmountInterfaceFactory;

class QrCodes implements BuilderInterface
{
    /**
     * Object containing the QR Codes linked to an order.
     * Receives expiration date and order amount.
     */
    public const QR_CODES = 'qr_codes';

    public function __construct(
        private readonly QrCodeFactory $qrCodeFactory,
        private readonly AmountInterfaceFactory $amountFactory,
        private readonly ConfigQrCode $config
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $order = $paymentDataObject->getOrder();

        /** @var Order $orderModel */
        $orderModel = $payment->getOrder();

        $result = [];

        $amount = $this->amountFactory->create();
        $amount->setValue($order->getGrandTotalAmount());

        $expirationDate = $this->config->getQrCodeExpiration($orderModel->getStoreId());

        $qrCodes = $this->qrCodeFactory->create();
        $qrCodes->setAmount($amount->getData());
        $qrCodes->setExpirationDate($expirationDate);

        $result[self::QR_CODES][] = $qrCodes->getData();

        return $result;
    }
}
