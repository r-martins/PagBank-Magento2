<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface ChargeInterface - Charge data.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 * @see https://dev.pagbank.uol.com.br/reference/objeto-charge
 */
interface ChargeInterface
{
    /**
     * Unique identifier assigned to the charge.
     * Receives a string.
     * Characters limit: 64 characters.
     */
    public const REFERENCE_ID = 'reference_id';

    /**
     * Date and time the charge was created.
     * Receives a string in the format YYYY-MM-DDThh:mm:ss.sTZD.
     */
    public const CREATED_AT = 'created_at';

    /**
     * Datetime format (YYYY-MM-DDThh:mm:ss.sTZD).
     */
    public const DATETIME_FORMAT = 'Y-m-d\TH:i:s.vP';

    /**
     * Contains information on the amount to be charged.
     * Receives an object.
     * @see \RicardoMartins\PagBank\Api\Connect\AmountInterface
     */
    public const AMOUNT = 'amount';

    /**
     * Contains information about the payment method.
     * Receives an object.
     * @see \RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface
     */
    public const PAYMENT_METHOD = 'payment_method';
}
