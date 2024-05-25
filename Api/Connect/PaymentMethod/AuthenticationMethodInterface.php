<?php

namespace RicardoMartins\PagBank\Api\Connect\PaymentMethod;

/**
 * Interface AuthenticationMethodInterface - Payment method authentication object.
 * @see https://dev.pagbank.uol.com.br/reference/objeto-order
 * @see https://dev.pagbank.uol.com.br/reference/criar-pagar-pedido-com-3ds-validacao-pagbank
 * @see https://dev.pagbank.uol.com.br/reference/criar-pagar-pedido-com-3ds-validacao-pagbank#crie-e-pague-o-pedido
 */
interface AuthenticationMethodInterface
{
    /**
     * Authentication method type.
     * Receives a string value.
     * ENUM: Only THREEDS or INAPP are accepted.
     * THREEDS: 3D Secure authentication.
     * INAPP: In-app authentication.
     */
    public const TYPE = 'type';

    /**
     * 3D Secure authentication id.
     * Receives a string value.
     * Is obtained from the PagSeguro.authenticate3DS() method.
     */
    public const ID = 'id';

    /**
     * Authentication method type THREEDS.
     */
    public const TYPE_THREEDS = 'THREEDS';
}
