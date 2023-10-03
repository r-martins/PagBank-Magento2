<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Checkout\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;

class Visibility implements VisibilityConditionInterface
{
    /**
     * Payment method prefix.
     */
    public const PAYMENT_METHOD_PREFIX = 'ricardomartins_pagbank';

    /**
     * Unique condition name.
     *
     * @var string
     */
    private static $conditionName = 'can_view_ricardomartins_pagbank_payment_info_block';

    public function __construct(
        private readonly Session $checkoutSession
    ) {}

    /**
     * @inheritDoc
     */
    public function isVisible(array $arguments)
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $methodCode = $payment->getMethod();

        return str_starts_with($methodCode, self::PAYMENT_METHOD_PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::$conditionName;
    }
}
