<?php

namespace RicardoMartins\PagBank\Api\Connect;

interface ResponseInterface
{
    /**
     * PagBank order identifier.
     * Format: ORDE_XXXXXXXXXXXX
     */
    public const PAGBANK_ORDER_ID = 'id';

    /**
     * Charges array
     */
    public const CHARGES = 'charges';

    /**
     * Charge status
     */
    const CHARGE_STATUS = 'status';

    /**
     * Charge id
     * Format: CHA_XXXXXXXXXXXX
     */
    const CHARGE_ID = 'id';

    /**
     * Charge authorized status.
     */
    public const STATUS_AUTHORIZED = 'AUTHORIZED';

    /**
     * Charge paid status.
     */
    public const STATUS_PAID = 'PAID';

    /**
     * Charge waiting status.
     * PagBang is analyzing the transaction.
     */
    public const STATUS_IN_ANALYSIS = 'IN_ANALYSIS';

    /**
     * Charge declined status.
     * PagBang or the issuer declined the transaction.
     */
    public const STATUS_DECLINED = 'DECLINED';

    /**
     * Charge canceled status.
     * PagBang or the issuer canceled the transaction.
     */
    public const STATUS_CANCELED = 'CANCELED';

    /**
     * Charge denied status.
     * PagBang or the issuer denied the transaction.
     */
    public const STATUS_DENIED = 'DENIED';

    /**
     * Order reference id
     */
    const REFERENCE_ID = 'reference_id';

    /**
     * Payment response
     */
    const PAYMENT_RESPONSE = 'payment_response';

    /**
     * Payment response code
     */
    const PAYMENT_RESPONSE_CODE = 'code';

    /**
     * Payment response message
     */
    const PAYMENT_RESPONSE_MESSAGE = 'message';

    /**
     * Payment response reference
     */
    const PAYMENT_RESPONSE_REFERENCE = 'reference';

    /**
     * Payment method data
     */
    const PAYMENT_METHOD = 'payment_method';

    /**
     * Payment method type
     */
    const PAYMENT_METHOD_TYPE = 'type';

    /**
     * Boleto data
     */
    const BOLETO = 'boleto';

    /**
     * Boleto ID
     */
    const BOLETO_ID = 'id';

    /**
     * Boleto barcode
     */
    const BOLETO_BARCODE = 'barcode';

    /**
     * Boleto formatted barcode
     */
    const BOLETO_FORMATED_BARCODE = 'formatted_barcode';

    /**
     * Boleto due date
     */
    const BOLETO_DUE_DATE = 'due_date';

    /**
     * Credit card data
     */


    /**
     * QrCodes Data (Pix)
     */
    public const QR_CODES = 'qr_codes';

    /**
     * QrCode id
     * Format: QRCO_XXXXXXXXXXXX
     */
    const QRCODE_ID = 'id';
}
