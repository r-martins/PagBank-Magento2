<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Adminhtml\System;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use RicardoMartins\PagBank\Model\Partner\Detector;

class SoftDescriptorComment implements CommentInterface
{
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue): string
    {
        $key = (string) $this->scopeConfig->getValue('payment/ricardomartins_pagbank/connect_key');
        if (Detector::fromKey($key)->isVindi()) {
            return 'A Vindi não usa soft descriptor neste módulo. O nome na fatura é configurado na conta Vindi.';
        }

        return '';
    }
}
