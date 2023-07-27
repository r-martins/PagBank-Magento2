<?php

namespace RicardoMartins\PagBank\Block\Adminhtml\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;

class Fieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public function __construct(
        private \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer,
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
        $html .= '<div class="heading"><strong>' . $element->getLegend() . '</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }
        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        return $html;
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
