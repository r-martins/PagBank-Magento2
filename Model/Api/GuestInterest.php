<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Quote\Model\QuoteIdMaskFactory;
use RicardoMartins\PagBank\Api\GuestInterestInterface;
use RicardoMartins\PagBank\Api\InterestInterface;

class GuestInterest implements GuestInterestInterface
{
    public function __construct(
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        private InterestInterface $interest
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(string $cartId, int $installment, int $creditCardBin)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $cartId = (int) $quoteIdMask->getQuoteId();
        return $this->interest->execute($cartId, $installment, $creditCardBin);
    }
}
