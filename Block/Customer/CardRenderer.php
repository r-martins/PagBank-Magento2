<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
class CardRenderer extends AbstractCardRenderer
{

    /**
     * @inheritDoc
     */
    public function getNumberLast4Digits()
    {
        return $this->getTokenDetails()['cc_last4'];
    }

    /**
     * @inheritDoc
     */
    public function getExpDate()
    {
        $month = $this->getTokenDetails()['cc_exp_month'];
        $year = $this->getTokenDetails()['cc_exp_year'];
        return "{$month}/{$year}";
    }

    /**
     * @inheritDoc
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['cc_type'])['url'];
    }

    /**
     * @inheritDoc
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['cc_type'])['height'];
    }

    /**
     * @inheritDoc
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['cc_type'])['width'];
    }

    /**
     * @inheritDoc
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === \RicardoMartins\PagBank\Gateway\Config\ConfigCc::METHOD_CODE;
    }
}
