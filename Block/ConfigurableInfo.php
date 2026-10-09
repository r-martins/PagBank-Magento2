<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block;

use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use RicardoMartins\PagBank\Model\Partner\Branding;
use RicardoMartins\PagBank\Model\Pix\QrImage;

class ConfigurableInfo extends \Magento\Payment\Block\ConfigurableInfo
{
    protected $_template = 'RicardoMartins_PagBank::info/pagbank.phtml';

    public function __construct(
        private readonly QrImage $qrImage,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Gateway\ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
    }

    private const FIELD_LABELS = [
        'payment_id' => 'Payment ID',
        'charge_id' => 'Charge ID',
        'threed_secure_id' => '3DS ID',
        'charge_link' => 'View on PagBank',
        'status' => 'Status',
        'installments' => 'Installments',
        'brand' => 'Card brand',
        'cc_owner' => 'Cardholder name',
        'cc_last_4' => 'Last 4 digits of the card',
        'authorization_code' => 'Authorization code',
        'nsu' => 'NSU',
        'expiration_date' => 'Expiration date',
        'payment_link_boleto_pdf' => 'Download ticket file',
        'payment_link_boleto_image' => 'Print ticket file',
        'payment_link_qrcode' => 'QR Code Pix',
        'payment_text_boleto' => 'Barcode',
        'payment_text_pix' => 'Pix Code',
        'is_sandbox' => 'Sandbox mode',
    ];

    private const FIELD_VALUES = [
        'expiration_date' => 'date',
        'charge_link' => 'link',
        'payment_link_boleto_pdf' => 'link',
        'payment_link_boleto_image' => 'link',
        'payment_link_qrcode' => 'image',
        'payment_text_boleto' => 'copy',
        'payment_text_pix' => 'copy',
        'is_sandbox' => 'yesno'
    ];

    /**
     * Sets data to transport
     *
     * @param DataObject $transport
     * @param string $field
     * @param string $value
     * @return void
     */
    protected function setDataToTransfer(
        DataObject $transport,
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
        if ($field === 'charge_link') {
            $partner = '';
            $info = $this->getInfo();
            if ($info) {
                $partner = (string) $info->getAdditionalInformation('partner');
            }

            return __(Branding::forOrderPartner($partner)->viewChargeLabel());
        }

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

            if ($field === 'payment_link_qrcode') {
                $order = $this->getInfo() ? $this->getInfo()->getOrder() : null;
                $value = $this->qrImage->displayUrl($order, (string) $value);
            }

            return [
                self::FIELD_VALUES[$field] => $value
            ];
        }

        return $value;
    }
}
