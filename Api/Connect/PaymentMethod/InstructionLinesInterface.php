<?php

namespace RicardoMartins\PagBank\Api\Connect\PaymentMethod;

/**
 * Interface InstructionLinesInterface - Payment instructions lines.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 */
interface InstructionLinesInterface
{
    /**
     * Payment instructions line 1.
     * Receives a string.
     * Characters limit: 75 characters.
     */
    public const INSTRUCTION_LINE_ONE = 'line_1';

    /**
     * Payment instructions line 2.
     * Receives a string.
     * Characters limit: 75 characters.
     */
    public const INSTRUCTION_LINE_TWO = 'line_2';
}
