<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface ConnectInterface - Customer data.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface CustomerInterface
{
    /**
     * Customer name.
     * Receive a string.
     * Character limit: 30 characters.
     */
    public const NAME = 'name';

    /**
     * Customer document. CPF or CNPJ is required.
     * CPF has 11 digits and CNPJ has 14 digits.
     * Receive a string.
     */
    public const TAX_ID = 'tax_id';

    /**
     * Customer email
     * Receive a string.
     * Character limit: 10 to 255 characters.
     */
    public const EMAIL = 'email';

    /**
     * Customer phones.
     * Receive an array of phones.
     * @see \RicardoMartins\PagBank\Api\Connect\PhoneInterface
     */
    public const PHONES = 'phones';
}
