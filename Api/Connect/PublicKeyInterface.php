<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface PublicKeyInterface - Public key create.
 * @see https://dev.pagbank.uol.com.br/reference/criar-chave-publica
 */
interface PublicKeyInterface
{
    /**
     * Public key.
     * Used to encrypt the card data.
     */
    public const PUBLIC_KEY = 'public_key';

    /**
     * Public key type.
     * Send the type of public key.
     */
    public const TYPE = 'type';

    /**
     * Public key type card.
     */
    public const TYPE_CARD = 'card';

    /**
     * Response error key
     */
    public const RESPONSE_ERROR = 'error';

    /**
     * Public key config path.
     * Used to save the public key.
     */
    public const PUBLIC_KEY_CONFIG_PATH = 'payment/ricardomartins_pagbank/public_key';

    /**
     * @param string $connectKey
     * @return string
     */
    public function createPublicKey(string $connectKey): string;

    /**
     * @param string $publicKey
     * @return void
     */
    public function savePublicKey(string $publicKey): void;
}
