<?php

namespace RicardoMartins\PagBank\Block\Checkout\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Info extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        private readonly Session $checkoutSession,
        Context $context
    ) {
        parent::__construct($context);
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
        $order = $this->checkoutSession->getLastRealOrder();

        $payment = $order->getPayment();
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
        $order = $this->checkoutSession->getLastRealOrder();

        $payment = $order->getPayment();
        $methodCode = $payment->getMethod();
        $additionalInfo = $payment->getAdditionalInformation();

        $blockName = match ($methodCode) {
            'ricardomartins_pagbank_boleto' => 'additional.info.ricardomartins.pagbank.boleto',
            'ricardomartins_pagbank_pix' => 'additional.info.ricardomartins.pagbank.pix',
            default => ''
        };

        if ($blockName) {
            $this->_layout->getBlock($blockName)->setData($additionalInfo);
        }

        return $this->_layout->renderElement($blockName);
    }

    private function getCssClassByPaymentMethod($code)
    {
        return match ($code) {
            'ricardomartins_pagbank_cc' => 'credit-card',
            'ricardomartins_pagbank_boleto' => 'boleto',
            'ricardomartins_pagbank_pix' => 'pix',
            default => ''
        };
    }
}
