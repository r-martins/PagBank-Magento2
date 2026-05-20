<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Test\Unit\Model\Request\Customer;

use PHPUnit\Framework\TestCase;
use RicardoMartins\PagBank\Model\Request\Customer\StreetAddressMapper;

class StreetAddressMapperTest extends TestCase
{
    private StreetAddressMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new StreetAddressMapper();
    }

    public function testFourLinesStandardBrazilianAddress(): void
    {
        $result = $this->mapper->mapFromStreetLines([
            'Rua das Flores',
            '123',
            'Apto 4',
            'Centro',
        ]);

        $this->assertSame('Rua das Flores', $result['street']);
        $this->assertSame('123', $result['number']);
        $this->assertSame('Apto 4', $result['complement']);
        $this->assertSame('Centro', $result['locality']);
    }

    public function testThreeLinesUsesLastLineAsLocalityWithoutComplement(): void
    {
        $result = $this->mapper->mapFromStreetLines([
            'Rua das Flores',
            '123',
            'Jardim América',
        ]);

        $this->assertSame('Rua das Flores', $result['street']);
        $this->assertSame('123', $result['number']);
        $this->assertNull($result['complement']);
        $this->assertSame('Jardim América', $result['locality']);
    }

    public function testTwoLinesUsesPlaceholderForLocality(): void
    {
        $result = $this->mapper->mapFromStreetLines([
            'Rua das Flores',
            '123 - Centro',
        ]);

        $this->assertSame('Rua das Flores', $result['street']);
        $this->assertSame('123 - Centro', $result['number']);
        $this->assertNull($result['complement']);
        $this->assertSame(StreetAddressMapper::LOCALITY_NOT_INFORMED, $result['locality']);
    }

    public function testEmptyLinesAreIgnored(): void
    {
        $result = $this->mapper->mapFromStreetLines([
            'Rua A',
            '',
            'Bairro X',
        ]);

        $this->assertSame('Rua A', $result['street']);
        $this->assertNull($result['complement']);
        $this->assertSame('Bairro X', $result['locality']);
    }
}
