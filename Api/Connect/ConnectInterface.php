<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface ConnectInterface
 */
interface ConnectInterface
{
    /**
     * WS URI
     * @var string
     */
    public const WS_URI = 'https://ws.ricardomartins.net.br/';

    /**
     * WS Endpoint
     * @var string
     */
    public const WS_ENDPOINT = 'pspro/v7/connect/ws/';

    /**
     * Public Key WS Endpoint.
     * This endpoint is used to get the public key to encrypt the card data.
     * Request Method: POST
     * @var string
     */
    public const WS_ENDPOINT_PUBLIC_KEY = self::WS_URI . self::WS_ENDPOINT . 'public-keys';

    /**
     * Orders WS Endpoint.
     * This endpoint is used to create a new order.
     * Request Method: POST
     * @var string
     */
    public const WS_ENDPOINT_ORDERS = self::WS_URI . self::WS_ENDPOINT . 'orders';

    /**
     * Interest WS Endpoint.
     * This endpoint is used to calculate the installments and the interest of a charge.
     * Request Method: GET
     * @var string
     */
    public const WS_ENDPOINT_INTEREST = self::WS_URI . self::WS_ENDPOINT . 'charges/fees/calculate';

    /**
     * Payment Info WS Endpoint.
     * This endpoint is used to get the information of a payment.
     * Request Method: GET
     */
    public const WS_ENDPOINT_PAYMENT_INFO = self::WS_URI . self::WS_ENDPOINT . 'orders';

    /**
     * Notification Endpoint
     * This endpoint is used to receive notifications about the status of a charge.
     * Request Method: POST
     * @var string
     */
    public const NOTIFICATION_ENDPOINT = 'pagbank/notification';

    /**
     * Sandbox Param.
     * This param is used to indicate that the request is a test.
     * @var string
     */
    public const SANDBOX_PARAM = 'isSandbox=1';

    /**
     * Sandbox Prefix.
     * This prefix is used to indicate that the Connect Key is from a test account.
     * @var string
     */
    public const SANDBOX_PREFIX = 'CONSANDBOX';

    /**
     * Transaction Details URL from PagBank Dashboard.
     * Do not use if you are using the Sandbox.
     */
    public const PAGBANK_TRANSACTION_DETAILS_URL = 'https://minhaconta.pagseguro.uol.com.br/transacao/detalhes';
}
