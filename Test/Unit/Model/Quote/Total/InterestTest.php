<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Test\Unit\Model\Quote\Total;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\TestCase;
use RicardoMartins\PagBank\Model\Quote\Total\Interest;

class InterestTest extends TestCase
{
    private function createQuote(string $method): Quote
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')->willReturn($method);

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPayment'])
            ->getMock();
        $quote->method('getPayment')->willReturn($payment);

        return $quote;
    }

    private function createTotal(): Total
    {
        return (new ObjectManager($this))->getObject(Total::class);
    }

    private function createShippingAssignment(): ShippingAssignmentInterface
    {
        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([new \stdClass()]);
        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($this->createMock(Address::class));
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        return $shippingAssignment;
    }

    public function testCollectAddsBaseInterestToBaseTotals(): void
    {
        $quote = $this->createQuote('ricardomartins_pagbank_cc');
        $quote->setData('ricardomartins_pagbank_interest_amount', 12.5);
        $quote->setData('ricardomartins_pagbank_base_interest_amount', 10.0);
        $total = $this->createTotal();

        $collector = new Interest(['ricardomartins_pagbank_cc']);
        $collector->collect($quote, $this->createShippingAssignment(), $total);

        $this->assertSame(12.5, $total->getTotalAmount('ricardomartins_pagbank_interest_amount'));
        $this->assertSame(10.0, $total->getBaseTotalAmount('ricardomartins_pagbank_base_interest_amount'));
    }

    public function testCollectForOtherMethodClearsBaseInterest(): void
    {
        $quote = $this->createQuote('checkmo');
        $quote->setData('ricardomartins_pagbank_interest_amount', 12.5);
        $quote->setData('ricardomartins_pagbank_base_interest_amount', 10.0);
        $total = $this->createTotal();

        $collector = new Interest(['ricardomartins_pagbank_cc']);
        $collector->collect($quote, $this->createShippingAssignment(), $total);

        $this->assertSame(0, $quote->getData('ricardomartins_pagbank_interest_amount'));
        $this->assertSame(0, $quote->getData('ricardomartins_pagbank_base_interest_amount'));
        $this->assertArrayNotHasKey('ricardomartins_pagbank_interest_base_amount', $quote->getData());
    }
}
