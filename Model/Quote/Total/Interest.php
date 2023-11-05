<?php

namespace RicardoMartins\PagBank\Model\Quote\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;

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

        if ($quote->getPayment()->getMethod() != ConfigCc::METHOD_CODE) {
            $this->clearValues($quote, $total);
            return $this;
        }

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
     * @param Quote $quote
     * @param Total $total
     * @return void
     */
    protected function clearValues(Quote $quote, Total $total): void
    {
        $interest = 0;
        $total->setTotalAmount('ricardomartins_pagbank_interest_amount', $interest);
        $total->setBaseTotalAmount('ricardomartins_pagbank_base_interest_amount', $interest);
        $quote->setData('ricardomartins_pagbank_interest_amount', $interest);
        $quote->setData('ricardomartins_pagbank_interest_base_amount', $interest);
    }
}
