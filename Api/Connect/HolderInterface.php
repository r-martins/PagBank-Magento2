<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface HolderInterface
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface HolderInterface
{
    /**
     * Customer name
     * Receives a string with 1 to 30 characters.
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
     * Must be in lowercase.
     * Receive a string with 10 - 255 characters.
     */
    public const EMAIL = 'email';

    /**
     * Customer address.
     * @see \RicardoMartins\PagBank\Api\Connect\AddressInterface
     */
    public const ADDRESS = 'address';
}
