<?php
/**
 * Bringit
 *
 * @category  Bringit
 * @package   Nupay
 * @version   1.0.0
 * @author    Ligia Salzano <ligia.salzano@proxysgroup.com>
 */

declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Validator;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order\Payment;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use RicardoMartins\PagBank\Plugin\Webapi\Controller\Rest;

class ResponseValidator extends AbstractValidator
{
    private array $responseErrorStatus = [
        ResponseInterface::STATUS_CANCELED,
        ResponseInterface::STATUS_DECLINED
    ];

    public function __construct(
        private readonly Rest $webapiRestPlugin,
        private readonly Logger $logger,
        ResultInterfaceFactory $resultFactory
    ) {
       parent::__construct($resultFactory);
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;

        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            $message = "There is something wrong with the gateway's response";
            $this->logger->debug(['message' => $message, 'validate' => $validationSubject]);
            return $this->createResult(false, [$message]);
        }

        $response = $validationSubject['response'];
        $errorMessages = [];

        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            $isValid = $validationResult[0] && $isValid;
            if (!$validationResult[0]) {
                $errorMessages = array_merge($errorMessages,$validationResult[1]);
            }
        }

        if (!$isValid) {
            /** Do not redirect to shipping step */
            $this->webapiRestPlugin->setClearHeader(true);

            /** @var PaymentDataObjectInterface $paymentDataObject */
            $paymentDataObject = $validationSubject['payment'];

            /** @var Payment $payment */
            $payment = $paymentDataObject->getPayment();
            $order = $payment->getOrder();

            $orderIncrementId = $order->getIncrementId();
            $data = [
                "Response Error for order #{$orderIncrementId}" => $errorMessages
            ];
            $this->logger->debug($data,null,true);
        }

        return $this->createResult($isValid,$errorMessages);
    }

    /**
     * @return array
     */
    private function getResponseValidators(): array
    {
        return [
            function ($response) {
                return [
                    !isset($response['error_messages']),
                    [__('There was a problem with the request.')]
                ];
            },
            function ($response) {
                $charge = isset($response['charges']) ? $response['charges'][0] : [];
                $status = isset($charge['status']) ? $charge['status'] : '';
                $message = isset($charge['payment_response']['message']) ? $charge['payment_response']['message'] : 'Payment not authorized.';
                return [
                    !in_array($status, $this->responseErrorStatus),
                    [__($message)],
                ];
            },
        ];
    }
}
