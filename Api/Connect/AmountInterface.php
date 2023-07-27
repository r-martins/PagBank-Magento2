<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface AmountInterface - Defines the order amount data.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface AmountInterface
{
    /**
     * Order amount value to be paid.
     * Receives an integer value in cents.
     * Characters limit: 9 digits.
     */
    public const VALUE = 'value';

    /**
     * ISO currency code with 3 characters.
     * Receives a string value in capital letters.
     * Characters limit: 3 characters.
     */
    public const CURRENCY = 'currency';

    /**
     * Brazilian Real currency code.
     */
    public const CURRENCY_BRL = 'BRL';
}
