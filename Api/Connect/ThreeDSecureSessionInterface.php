<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface ThreeDSecureSessionInterface - Get 3DSecure session.
 * @see https://dev.pagbank.uol.com.br/reference/criar-sessao-autenticacao-3ds
 */
interface ThreeDSecureSessionInterface
{
    /**
     * ThreeDSecure session key.
     * Used for authentication of operations.
     * The session value is sent in the header of the request.
     */
    public const THREE3D_SECURE_SESSION = 'session';

    /**
     * ThreeDSecure session expires at.
     * Session expires in 30 minutes.
     */
    public const THREE3D_SECURE_SESSION_EXPIRES_AT = 'expires_at';

    /**
     * ThreeDSecure session environment.
     */
    public const CONNECT_ENVIRONMENT = 'environment';

    /**
     * @return string
     */
    public function createThreeDSecureSession(): string;
}
