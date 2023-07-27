<?php

namespace RicardoMartins\PagBank\Api\Connect\PaymentMethod;

/**
 * Interface QrCodeInterface - QR Code object.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface QrCodeInterface
{
    /**
     * Expiration date of the QR Code. By default, the QR Code expires in 24 hours.
     * Receives a string in the format YYYY-MM-DDThh:mm:ss.sTZD.
     */
    public const EXPIRATION_DATE = 'expiration_date';

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
}
