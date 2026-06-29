<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use RicardoMartins\PagBank\Model\DocumentNormalizer;

class DocumentNormalizerTest extends TestCase
{
    private DocumentNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DocumentNormalizer();
    }

    public function testNormalizeCpfRemovesMask(): void
    {
        $this->assertSame('12345678901', $this->normalizer->normalize('123.456.789-01'));
    }

    public function testNormalizeCnpjNumericRemovesMask(): void
    {
        $this->assertSame('12345678000195', $this->normalizer->normalize('12.345.678/0001-95'));
    }

    public function testNormalizeCnpjAlphanumeric(): void
    {
        $this->assertSame('AB12CD34EF56GH', $this->normalizer->normalize('AB12.CD34/EF56-GH'));
    }

    public function testNormalizeUppercasesAlphanumericCnpj(): void
    {
        $this->assertSame('AB12CD34EF56GH', $this->normalizer->normalize('ab12cd34ef56gh'));
    }

    public function testIsCnpj(): void
    {
        $this->assertTrue($this->normalizer->isCnpj('12.345.678/0001-95'));
        $this->assertTrue($this->normalizer->isCnpj('AB12CD34EF56GH'));
        $this->assertFalse($this->normalizer->isCnpj('123.456.789-01'));
    }
}
