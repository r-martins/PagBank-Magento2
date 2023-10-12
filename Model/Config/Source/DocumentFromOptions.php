<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DocumentFromOptions implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'taxvat', 'label' => __('Customer: TAX/VAT number (taxvat)')],
            ['value' => 'vat_id', 'label' => __('Billing Address: VAT Number (vat_id)')],
            ['value' => 'payment_form', 'label' => __('Checkout: Request in the payment form')]
        ];
    }
}
