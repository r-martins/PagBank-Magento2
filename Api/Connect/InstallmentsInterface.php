<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface InstallmentsInterface - Installments for payment methods.
 * @see https://dev.pagbank.uol.com.br/reference/consultar-juros-transacao
 * @see https://dev.pagbank.uol.com.br/reference/criar-transacao-com-repasse-de-juros
 */
interface InstallmentsInterface
{
    /**
     * Payment methods from which the integrator would like to recover fees for transfer.
     * Receives an array of strings.
     */
    public const PAYMENT_METHODS = 'payment_methods';

    /**
     * Payment method type.
     * Receives a string.
     */
    public const PAYMENT_METHOD_TYPE_CC = 'CREDIT_CARD';

    /**
     * Transaction amount in cents.
     * Receives an integer.
     */

    public const VALUE = 'value';

    /**
     * Maximum number of installments allowed.
     * Receives an integer.
     */
    public const MAX_INSTALLMENTS = 'max_installments';

    /**
     * Maximum number of installments without interest for the customer.
     * The seller will assume the fees for the installments.
     * Receives an integer.
     */
    public const MAX_INSTALLMENTS_NO_INTEREST = 'max_installments_no_interest';

    /**
     * Credit card bin.
     * The first 6 digits of the credit card.
     * Receives an integer.
     */
    public const CREDIT_CARD_BIN = 'credit_card_bin';
}
