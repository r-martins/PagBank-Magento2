<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Partner;

/**
 * Migration cutoff — display/CTA only. Server enforces the real cutover.
 * Mirror of pbintegracoes.com Cutoff / Woo Partner\Cutoff / Magento 1.
 */
class Cutoff
{
    public const ISO = '2026-11-05T00:00:00-03:00';

    public const LABEL = '5 de novembro de 2026, 00:00 (horário de Brasília)';

    public const SHORT = '5/nov/2026';

    public const MIGRATE_URL = 'https://pbintegracoes.com/migrar';

    public static function timestamp(): int
    {
        $timestamp = strtotime(self::ISO);

        return $timestamp ? (int) $timestamp : 0;
    }

    public static function isPast(): bool
    {
        $timestamp = self::timestamp();

        return $timestamp > 0 && time() >= $timestamp;
    }
}
