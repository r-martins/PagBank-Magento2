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
     * WS SDK Endpoint
     * @var string
     */
    const WS_SDK_ENDPOINT = 'pspro/v7/connect/ws-sdk/';

    /**
     * Public Key WS Endpoint.
     * This endpoint is used to get the public key to encrypt the card data.
     * Request Method: POST
     * @var string
     */
    public const WS_ENDPOINT_PUBLIC_KEY = self::WS_URI . self::WS_ENDPOINT . 'public-keys';

    /**
     * Public Key Validate WS Endpoint.
     * This endpoint is used to validate the public key.
     * Request Method: GET
     * @var string
     */
    public const WS_ENDPOINT_PUBLIC_KEY_VALIDATE = self::WS_URI . self::WS_ENDPOINT . 'public-keys/card';

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
     * Checkout SDK Session Endpoint
     * This endpoint allows you to generate a session that is used to authenticate operations.
     * The generated session indicates who is the merchant who owns the interactions made.
     * You need the session when using processes that involve PagBank's internal 3DS authentication with SDK.
     * Request Method: POST
     * @see https://dev.pagbank.uol.com.br/docs/criar-pagar-pedido-com-3ds-validacao-pagbank#adicione-e-configure-o-sdk-pagbank
     * @var string
     */
    const CHECKOUT_SDK_SESSION_ENDPOINT = self::WS_URI . self::WS_SDK_ENDPOINT . 'checkout-sdk/sessions';

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

    /**
     * Next Auto Update Date.
     * Used to store the next date to update the order by cron.
     * This date is used by the cron to update the order status when the order is not updated by the notification.
     * @see \RicardoMartins\PagBank\Gateway\Response\OrderUpdaterCronHandler
     * @var string
     */
    public const PAGBANK_NEXT_AUTO_UPDATE_DATE = 'ricardomartins_pagbank_next_auto_update_date';

    /**
     * Charges.
     * Used to store the md5 hash of the charges of the order.
     * This field is used to compare the charges of the order to update the order status.
     * @see \RicardoMartins\PagBank\Cron\SalesOrderUpdater
     * @var string
     */
    public const PAGBANK_CHARGES = 'ricardomartins_pagbank_charges';
}
