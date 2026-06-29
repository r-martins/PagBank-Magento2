<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Model;

/**
 * Normalize CPF/CNPJ for PagBank API submission.
 *
 * CPF remains numeric (11 chars). CNPJ may be alphanumeric (14 chars) per BACEN rules.
 */
class DocumentNormalizer
{
    public function normalize(?string $document): string
    {
        $document = $document !== null ? (string) $document : '';
        $document = preg_replace('/[.\-\/]/', '', $document) ?? '';

        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $document) ?? '');
    }

    public function isCnpj(?string $document): bool
    {
        return strlen($this->normalize($document)) === 14;
    }
}
