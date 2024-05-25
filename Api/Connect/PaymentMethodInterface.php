<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface PaymentMethodInterface - Payment method data.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 * @see https://dev.pagbank.uol.com.br/reference/objeto-charge
 */
interface PaymentMethodInterface
{
    /**
     * Payment method type.
     * Receives a string value.
     * ENUM: Only BOLETO, CREDIT_CARD or DEBIT_CARD are accepted.
     */
    public const TYPE = 'type';

    /**
     * Payment method type Boleto.
     */
    public const TYPE_BOLETO = 'BOLETO';

    /**
     * Payment method type Credit Card.
     */
    public const TYPE_CREDIT_CARD = 'CREDIT_CARD';

    /**
     * Installments number.
     * Required for credit card payments.
     * Receives an integer value.
     * Character limit: 2 digits.
     */
    public const INSTALLMENTS = 'installments';

    /**
     * Capture flag.
     * Required for credit card payments.
     * Receives a boolean value.
     * If true, the payment will be captured automatically.
     * If false, the payment will be pre-authorized and must be captured later.
     */
    public const CAPTURE = 'capture';

    /**
     * Soft descriptor. Optional. Only for credit card payments.
     * Receives a string value.
     * The soft descriptor is the name of the company that will appear in the customer's credit card statement.
     * Character limit: 17 characters.
     */
    public const SOFT_DESCRIPTOR = 'soft_descriptor';

    /**
     * Card data. Required for credit card payments.
     * @see \RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterface
     */
    public const TYPE_CREDIT_CARD_OBJECT = 'card';

    /**
     * Boleto data. Required for boleto payments.
     * @see \RicardoMartins\PagBank\Api\Connect\PaymentMethod\BoletoInterface
     */
    public const TYPE_BOLETO_OBJECT = 'boleto';

    /**
     * Authentication method data. Optional.
     * Used for 3D Secure authentication.
     * @see \RicardoMartins\PagBank\Api\Connect\PaymentMethod\AuthenticationMethodInterface
     */
    public const AUTHENTICATION_METHOD = 'authentication_method';
}
