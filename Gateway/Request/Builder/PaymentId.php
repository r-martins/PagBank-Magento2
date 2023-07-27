<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;

class PaymentId implements BuilderInterface
{
    /**
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();

        $transaction = $this->transactionRepository->getByTransactionType(
            TransactionInterface::TYPE_ORDER,
            $payment->getId()
        );

        return [
            ResponseInterface::PAGBANK_ORDER_ID => $transaction->getTxnId(),
        ];
    }
}
