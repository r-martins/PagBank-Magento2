<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Test\Unit\Model\Api;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;
use RicardoMartins\PagBank\Gateway\Http\TransferFactory\GetInterestsTransferFactory;
use RicardoMartins\PagBank\Model\Api\Interest;
use RicardoMartins\PagBank\Model\Request\InstallmentsFactory;

class InterestTest extends TestCase
{
    public function testExecuteStoresBaseInterestUnderSchemaColumnName(): void
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository->method('getActive')->willReturn($quote);
        $cartRepository->expects($this->once())->method('save')->with($quote);

        $cartTotalRepository = $this->createMock(CartTotalRepositoryInterface::class);
        $cartTotalRepository->method('get')->willReturn($this->createMock(TotalsInterface::class));

        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session->method('__call')->with('getInstallments')->willReturn([
            [
                'installments' => 3,
                'interest_free' => false,
                'amount' => ['fees' => ['buyer' => ['interest' => ['total' => 1250]]]],
            ],
        ]);

        $interest = new Interest(
            $cartRepository,
            $cartTotalRepository,
            $this->createMock(ConfigCc::class),
            $this->createMock(InstallmentsFactory::class),
            $this->createMock(GetInterestsTransferFactory::class),
            $this->createMock(GeneralClient::class),
            $session
        );

        $this->assertTrue($interest->execute(1, 3, 411111));
        $this->assertSame(12.5, $quote->getData('ricardomartins_pagbank_interest_amount'));
        $this->assertSame(12.5, $quote->getData('ricardomartins_pagbank_base_interest_amount'));
        $this->assertArrayNotHasKey('ricardomartins_pagbank_interest_base_amount', $quote->getData());
    }
}
