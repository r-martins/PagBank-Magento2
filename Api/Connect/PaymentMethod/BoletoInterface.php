<?php

namespace RicardoMartins\PagBank\Api\Connect\PaymentMethod;

/**
 * Interface BoletoInterface - Boleto payment object.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface BoletoInterface
{
    /**
     * Payment due date.
     * Receives a string in the format: yyyy-MM-dd
     * Characters limit: 10
     */
    public const DUE_DATE = 'due_date';

    /**
     * Boleto payment instructions.
     * Receives an array with the following keys:
     * - line_1: string
     * - line_2: string
     * @see \RicardoMartins\PagBank\Api\Connect\PaymentMethod\InstructionLinesInterface
     */
    public const INSTRUCTION_LINES = 'instruction_lines';

    /**
     * Contains the boleto holder data.
     * @see \RicardoMartins\PagBank\Api\Connect\HolderInterface
     */
    public const HOLDER = 'holder';

    /**
     * Customer billing address
     * @see \RicardoMartins\PagBank\Api\Connect\AddressInterface
     */
    public const HOLDER_ADDRESS = 'address';
}
