<?php

namespace RicardoMartins\PagBank\Block\Adminhtml\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use RicardoMartins\PagBank\Gateway\Config\Config as GatewayConfig;
use RicardoMartins\PagBank\Model\Partner\Cutoff;

class Fieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public function __construct(
        private \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer,
        private GatewayConfig $gatewayConfig,
        Context $context,
        Session $authSession,
        Js $jsHelper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _getFrontendClass($element): string
    {
        $newClasses = 'with-button enabled';
        return parent::_getFrontendClass($element) . " {$newClasses}";
    }

    /**
     * @inheritDoc
     */
    protected function _getHeaderTitleHtml($element)
    {
        $htmlId = $element->getHtmlId();

        $html = '<div class="config-heading">';
        $html .= '<div class="button-container">';
        $html .= '<button type="button" class="button action-configure" '.
            'id="' . $htmlId . '-head"><span class="state-closed">' . __(
                'Configure'
            ) . '</span><span class="state-opened">' . __(
                'Close'
            ) . '</span></button>';

        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "rmPagbankToggleSolution.call(this, '"
            . $htmlId . "', '" . $this->getUrl('adminhtml/*/state') .
            "');event.preventDefault();",
            'button#' . $htmlId . '-head'
        );

        $html .= '</div>';
        $html .= '<div class="heading" style="background:none;padding-left:0;">';
        $html .= '<img src="' . $this->escapeUrl($this->getViewFileUrl('RicardoMartins_PagBank::images/logo-pbintegracoes.svg'))
            . '" alt="PB Integrações" width="153" height="36"'
            . ' style="display:block;height:36px;width:auto;margin:0 0 8px;" />';
        $html .= '<strong>' . $element->getLegend() . '</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }
        $html .= $this->getCutoffHtml();
        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Live cutoff countdown on the payment card.
     * The migrate link is only for stores still on a PagBank Connect Key.
     */
    private function getCutoffHtml(): string
    {
        $migrate = '';
        if (!$this->gatewayConfig->isVindi()) {
            $url = $this->escapeUrl(Cutoff::MIGRATE_URL);
            $migrate = Cutoff::isPast()
                ? ' <a href="' . $url . '" target="_blank" rel="noopener">Migrar para Vindi</a>.'
                : '<p style="margin:10px 0 0;"><a href="' . $url
                    . '" target="_blank" rel="noopener">Migrar agora</a></p>';
        }

        if (Cutoff::isPast()) {
            return '<div class="pb-cutoff" style="margin-top:12px;padding:12px 14px;background:#fde8e8;'
                . 'border:1px solid #e8b4b4;border-radius:4px;max-width:520px;">'
                . '<strong>Importante</strong>'
                . '<p style="margin:6px 0 0;">Corte PagBank atingido (' . Cutoff::SHORT . ').'
                . $migrate . '</p></div>';
        }

        return '<div class="pb-cutoff" style="margin-top:12px;padding:12px 14px;background:#fff8e6;'
            . 'border:1px solid #f0d58c;border-radius:4px;max-width:520px;">'
            . '<strong>Importante</strong>'
            . '<p style="margin:6px 0 10px;">PagBank continuará funcionando nas integrações PB até '
            . Cutoff::SHORT . '.</p>'
            . '<div class="pb-cutoff-countdown" data-until="' . Cutoff::timestamp()
            . '" style="display:flex;gap:8px;">'
            . $this->cutoffUnit('days', 'dias')
            . $this->cutoffUnit('hours', 'horas')
            . $this->cutoffUnit('minutes', 'min')
            . $this->cutoffUnit('seconds', 'seg')
            . '</div>'
            . $migrate
            . '</div>';
    }

    private function cutoffUnit(string $unit, string $label): string
    {
        return '<div style="min-width:4.25rem;padding:8px 10px;text-align:center;background:#fff;'
            . 'border-radius:8px;box-shadow:0 4px 12px rgba(18,41,93,.08);">'
            . '<div data-unit="' . $unit . '" style="font-size:1.6rem;font-weight:600;'
            . 'font-variant-numeric:tabular-nums;">—</div>'
            . '<div style="margin-top:2px;font-size:.7rem;font-weight:600;letter-spacing:.04em;'
            . 'text-transform:uppercase;color:#5c6b7a;">' . $label . '</div></div>';
    }

    /**
     * @inheritDoc
     */
    protected function _getExtraJs($element): string
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.rmPagbankToggleSolution = function (id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName(\"open\")) {
                    \$$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
            var root = document.querySelector('.pb-cutoff-countdown');
            if (!root) {
                return;
            }
            var until = parseInt(root.getAttribute('data-until'), 10) * 1000;
            var nodes = {
                days: root.querySelector('[data-unit=\"days\"]'),
                hours: root.querySelector('[data-unit=\"hours\"]'),
                minutes: root.querySelector('[data-unit=\"minutes\"]'),
                seconds: root.querySelector('[data-unit=\"seconds\"]')
            };
            var pad = function (n) { return (n < 10 ? '0' : '') + n; };
            var tick = function () {
                var diff = until - Date.now();
                if (diff <= 0) {
                    root.innerHTML = 'Corte PagBank atingido.';
                    return;
                }
                var total = Math.floor(diff / 1000);
                nodes.days.textContent = pad(Math.floor(total / 86400));
                nodes.hours.textContent = pad(Math.floor((total % 86400) / 3600));
                nodes.minutes.textContent = pad(Math.floor((total % 3600) / 60));
                nodes.seconds.textContent = pad(total % 60);
            };
            tick();
            window.setInterval(tick, 1000);
        });";

        return $this->_jsHelper->getScript($script);
    }

    /**
     * @inheritDoc
     */
    protected function _getHeaderCommentHtml($element): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function _isCollapseState($element): bool
    {
        return false;
    }
}
