<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Adminhtml\System;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use RicardoMartins\PagBank\Model\Partner\Detector;

abstract class AbstractPartnerComment implements CommentInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly RequestInterface $request,
    ) {
    }

    protected function isVindi(): bool
    {
        $store = (string) $this->request->getParam('store');
        $website = (string) $this->request->getParam('website');

        if ($store !== '') {
            $key = $this->scopeConfig->getValue(
                'payment/ricardomartins_pagbank/connect_key',
                ScopeInterface::SCOPE_STORES,
                $store
            );
        } elseif ($website !== '') {
            $key = $this->scopeConfig->getValue(
                'payment/ricardomartins_pagbank/connect_key',
                ScopeInterface::SCOPE_WEBSITES,
                $website
            );
        } else {
            $key = $this->scopeConfig->getValue('payment/ricardomartins_pagbank/connect_key');
        }

        return Detector::fromKey((string) $key)->isVindi();
    }
}
