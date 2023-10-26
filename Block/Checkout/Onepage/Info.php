<?php

namespace RicardoMartins\PagBank\Block\Checkout\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;

class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Payment method prefix.
     */
    public const PAYMENT_METHOD_PREFIX = 'ricardomartins_pagbank';

    /**
     * @var Order $order
     */
    private Order $order;

    public function __construct(
        private readonly Session $checkoutSession,
        Context $context
    ) {
        $this->order = $this->checkoutSession->getLastRealOrder();
        parent::__construct($context);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isVisible()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return Info
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $payment = $this->order->getPayment();
        $methodCode = $payment->getMethod();

        $this->addData(
            [
                'payment_method' => $methodCode,
                'payment_method_class' => $this->getCssClassByPaymentMethod($methodCode)
            ]
        );
    }

    /**
     * Render additional order information lines and return result html
     *
     * @return string
     */
    public function getAdditionalInfoHtml()
    {
        $payment = $this->order->getPayment();
        $methodCode = $payment->getMethod();
        $additionalInfo = $payment->getAdditionalInformation();

        $blockName = match ($methodCode) {
            ConfigBoleto::METHOD_CODE => 'additional.info.ricardomartins.pagbank.boleto',
            ConfigQrCode::METHOD_CODE => 'additional.info.ricardomartins.pagbank.pix',
            default => ''
        };

        if ($blockName) {
            $this->_layout->getBlock($blockName)->setData($additionalInfo);
        }

        return $this->_layout->renderElement($blockName);
    }

    /**
     * @return bool
     */
    private function isVisible()
    {
        try {
            $payment = $this->order->getPayment();
            $methodCode = $payment->getMethod();
        } catch (\Exception $e) {
            return false;
        }

        return str_starts_with($methodCode, self::PAYMENT_METHOD_PREFIX);
    }

    /**
     * @param $code
     * @return string
     */
    private function getCssClassByPaymentMethod($code)
    {
        return match ($code) {
            ConfigCc::METHOD_CODE => 'credit-card',
            ConfigBoleto::METHOD_CODE => 'boleto',
            ConfigQrCode::METHOD_CODE => 'pix',
            default => ''
        };
    }
}
