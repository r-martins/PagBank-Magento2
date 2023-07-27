<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface ItemInterface - Item interface.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface ItemInterface
{
    /**
     * The reference id of the item.
     * Receive a string.
     */
    public const REFERENCE_ID = 'reference_id';

    /**
     * The name of the item.
     * Receive a string.
     * Character limit: 64 characters.
     */
    public const NAME = 'name';

    /**
     * The quantity of the item.
     * Receive an integer (int32).
     * Character limit: 5 characters.
     */
    public const QUANTITY = 'quantity';

    /**
     * The unit amount of the item (in cents).
     * Receive an integer (int32).
     * Character limit: 9 characters.
     */
    public const UNIT_AMOUNT = 'unit_amount';
}
