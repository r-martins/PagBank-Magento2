<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use RicardoMartins\PagBank\Api\GuestListInstallmentsInterface;
use RicardoMartins\PagBank\Api\ListInstallmentsInterface;

class GuestListInstallments implements GuestListInstallmentsInterface
{
    public function __construct(
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        private ListInstallmentsInterface $listInstallments,
        private QuoteIdMaskResource $quoteIdMaskResource
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $cartId, int $creditCardBin)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $cartId, 'masked_id');
        $cartId = (int) $quoteIdMask->getQuoteId();
        return $this->listInstallments->execute($cartId, $creditCardBin);
    }
}
