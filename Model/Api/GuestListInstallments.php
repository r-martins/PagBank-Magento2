<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Quote\Model\QuoteIdMaskFactory;
use RicardoMartins\PagBank\Api\GuestListInstallmentsInterface;
use RicardoMartins\PagBank\Api\ListInstallmentsInterface;

class GuestListInstallments implements GuestListInstallmentsInterface
{
    public function __construct(
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        private ListInstallmentsInterface $listInstallments
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(string $cartId, int $creditCardBin)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $cartId = (int) $quoteIdMask->getQuoteId();
        return $this->listInstallments->execute($cartId, $creditCardBin);
    }
}
