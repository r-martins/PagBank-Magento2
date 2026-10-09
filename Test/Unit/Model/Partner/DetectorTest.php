<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Test\Unit\Model\Partner;

use PHPUnit\Framework\TestCase;
use RicardoMartins\PagBank\Model\Partner\Detector;
use RicardoMartins\PagBank\Model\Partner\Partner;

class DetectorTest extends TestCase
{
    public function testPagBankProductionKey(): void
    {
        $detector = Detector::fromKey('CON' . str_repeat('A', 37));

        $this->assertTrue($detector->isPagBank());
        $this->assertFalse($detector->isSandbox());
        $this->assertSame(Partner::PAGBANK, $detector->getPartner());
    }

    public function testPagBankSandboxKey(): void
    {
        $detector = Detector::fromKey('CONSANDBOX' . str_repeat('B', 30));

        $this->assertTrue($detector->isPagBank());
        $this->assertTrue($detector->isSandbox());
    }

    public function testVindiProductionAndTemplate(): void
    {
        $detector = Detector::fromKey('CONVD30' . str_repeat('C', 33));

        $this->assertTrue($detector->isVindi());
        $this->assertFalse($detector->isSandbox());
        $this->assertSame('30dias', $detector->getTemplateCode());
        $this->assertSame(Partner::VINDI, $detector->getPartner());
    }

    public function testVindiSandboxFlex(): void
    {
        $detector = Detector::fromKey('CONVDSANDBOXFLX' . str_repeat('D', 25));

        $this->assertTrue($detector->isVindi());
        $this->assertTrue($detector->isSandbox());
        $this->assertSame('flex', $detector->getTemplateCode());
        $this->assertSame(substr('CONVDSANDBOXFLX' . str_repeat('D', 25), -4), $detector->fingerprint());
    }

    public function testLegacyShortVindiKey(): void
    {
        $detector = Detector::fromKey('CONVD14ABC');

        $this->assertTrue($detector->isVindi());
        $this->assertSame('14dias', $detector->getTemplateCode());
    }
}
