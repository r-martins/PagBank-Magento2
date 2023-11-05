<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block;

use Magento\Framework\Phrase;

class ConfigurableInfo extends \Magento\Payment\Block\ConfigurableInfo
{
    protected $_template = 'RicardoMartins_PagBank::info/pagbank.phtml';

    private const FIELD_LABELS = [
        'payment_id' => 'Payment ID',
        'charge_id' => 'Charge ID',
        'charge_link' => 'Charge link',
        'status' => 'Status',
        'installments' => 'Installments',
        'cc_owner' => 'Cardholder name',
        'cc_last_4' => 'Last 4 digits of the card',
        'expiration_date' => 'Expiration date',
        'payment_link_boleto_pdf' => 'Download ticket file',
        'payment_link_boleto_image' => 'Print ticket file',
        'payment_link_qrcode' => 'QR Code Pix',
        'payment_text_boleto' => 'Código de barras',
        'payment_text_pix' => 'Código Pix',
    ];

    private const FIELD_VALUES = [
        'expiration_date' => 'date',
        'charge_link' => 'link',
        'payment_link_boleto_pdf' => 'link',
        'payment_link_boleto_image' => 'link',
        'payment_link_qrcode' => 'image',
        'payment_text_boleto' => 'copy',
        'payment_text_pix' => 'copy',
    ];

    /**
     * Sets data to transport
     *
     * @param \Magento\Framework\DataObject $transport
     * @param string $field
     * @param string $value
     * @return void
     */
    protected function setDataToTransfer(
        \Magento\Framework\DataObject $transport,
                                      $field,
                                      $value
    ) {
        $transport->setData(
            (string)$this->getLabel($field),
            $this->getValueView(
                $field,
                $value
            )
        );
    }

    /**
     * Returns label
     *
     * @param string $field
     * @return string | Phrase
     */
    protected function getLabel($field)
    {
        if (isset(self::FIELD_LABELS[$field])) {
            return __(self::FIELD_LABELS[$field]);
        }

        return $field;
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string $value
     * @return array | string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getValueView($field, $value)
    {
        if (isset(self::FIELD_VALUES[$field])) {
            if (self::FIELD_VALUES[$field] === 'date') {
                return $this->formatDate($value, \IntlDateFormatter::SHORT, true);
            }

            return [
                self::FIELD_VALUES[$field] => $value
            ];
        }

        return $value;
    }
}
