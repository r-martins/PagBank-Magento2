<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\Customer;

use RicardoMartins\PagBank\Api\Gateway\Data\AddressAdapterInterface;

/**
 * Maps Magento street lines to PagBank address fields.
 *
 * Supports stores configured with 2, 3 or 4 address lines (same rules as Magento 1 module).
 */
class StreetAddressMapper
{
    public const LOCALITY_NOT_INFORMED = '(bairro não informado)';

    /**
     * @return array{street: string, number: string, complement: string|null, locality: string}
     */
    public function map(AddressAdapterInterface $addressAdapter): array
    {
        return $this->mapFromStreetLines($addressAdapter->getStreet());
    }

    /**
     * @param string[] $streetLines
     * @return array{street: string, number: string, complement: string|null, locality: string}
     */
    public function mapFromStreetLines(array $streetLines): array
    {
        $lines = array_values(array_filter(
            $streetLines,
            static fn ($line) => trim((string) $line) !== ''
        ));

        $street = (string) ($lines[0] ?? '');
        $number = (string) ($lines[1] ?? '');
        $lineCount = count($lines);
        $locality = $lineCount > 2
            ? (string) end($lines)
            : self::LOCALITY_NOT_INFORMED;
        $complement = $lineCount > 3 ? (string) ($lines[2] ?? '') : null;

        return [
            'street' => $street,
            'number' => $number,
            'complement' => $complement,
            'locality' => $locality,
        ];
    }

    public function applyStreetFields(Address $address, AddressAdapterInterface $addressAdapter): void
    {
        $parts = $this->map($addressAdapter);

        $address->setStreet($parts['street']);
        $address->setNumber($parts['number']);

        if ($parts['complement'] !== null && $parts['complement'] !== '') {
            $address->setComplement($parts['complement']);
        }

        $address->setLocality($parts['locality']);
    }
}
