<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use RicardoMartins\PagBank\Api\GuestInterestInterface;
use RicardoMartins\PagBank\Api\InterestInterface;

class GuestInterest implements GuestInterestInterface
{
    public function __construct(
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        private InterestInterface $interest,
        private QuoteIdMaskResource $quoteIdMaskResource
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $cartId, int $installment, int $creditCardBin)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $cartId, 'masked_id');
        $cartId = (int) $quoteIdMask->getQuoteId();
        return $this->interest->execute($cartId, $installment, $creditCardBin);
    }
}
