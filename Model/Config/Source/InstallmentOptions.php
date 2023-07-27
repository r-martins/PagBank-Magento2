<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Config\Source;

class InstallmentOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'external', 'label' => __('Follow PagBank account settings (default)')],
            ['value' => 'buyer', 'label' => __('Interest paid by the buyer')],
            ['value' => 'fixed', 'label' => __('Up to X interest-free installments')],
            ['value' => 'min_total', 'label' => __('Up to X interest-free installments depending on the amount of the installment')]
        ];
    }
}
