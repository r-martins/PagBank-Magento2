<?php

namespace RicardoMartins\PagBank\Model\Quote\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class Interest extends AbstractTotal
{
    /**
     * Get Subtotal label.
     *
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Interest');
    }

    /**
     * @inheritDoc
     */
    public function fetch(
        Quote $quote,
        Total $total
    ): array
    {
        $result = [];
        $interest = $quote->getRicardomartinsPagbankInterestAmount();

        if ($interest) {
            $result = [
                'code'  => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $interest,
            ];
        }

        return $result;
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $interest = $quote->getRicardomartinsPagbankInterestAmount();
        $baseInterest = $quote->getBaseRicardomartinsPagbankInterestAmount();

        $total->setRicardomartinsPagbankInterestAmount($interest);
        $total->setBaseRicardomartinsPagbankInterestAmount($baseInterest);

        $total->setTotalAmount('ricardomartins_pagbank_interest_amount', $interest);
        $total->setBaseTotalAmount('ricardomartins_pagbank_base_interest_amount', $baseInterest);

        $total->setGrandTotal($total->getGrandTotal());
        $total->setBaseGrandTotal($total->getBaseGrandTotal());

        return $this;
    }

    /**
     * Clear Values.
     *
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
}
