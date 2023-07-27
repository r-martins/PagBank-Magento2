<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface PhoneInterface - Phone data.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface PhoneInterface
{
    /**
     * Phone country code (DDI).
     * Receive an integer (int32).
     * Character limit: 3.
     */
    public const COUNTRY = 'country';

    /**
     * Brazil country code (DDI).
     */
    const DEFAULT_COUNTRY_CODE = 55;

    /**
     * Phone area code (DDD).
     * Receive an integer (int32).
     * Character limit: 2.
     */
    public const AREA = 'area';

    /**
     * Phone number.
     * Receive an integer (int32).
     * Character limit: 8 or 9.
     */
    public const NUMBER = 'number';

    /**
     * Phone type.
     * Receive a string.
     * ENUM. Possible values: MOBILE, BUSINESS, HOME.
     */
    public const TYPE = 'type';

    /**
     * Phone type mobile.
     */
    public const TYPE_MOBILE = 'MOBILE';

    /**
     * Phone type business.
     */
    public const TYPE_BUSINESS = 'BUSINESS';

    /**
     * Phone type home.
     */
    public const TYPE_HOME = 'HOME';
}
