<?php
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
        $errorMessages = [];
        $errorCodes = [];

        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            $message = "There is something wrong with the gateway's response";
            $this->logger->debug(['message' => $message, 'validate' => $validationSubject]);
            $isValid = false;
            $errorMessages[] = $message;
            $errorCodes[] = 'INVALID_RESPONSE';
            return $this->createResult($isValid, $errorMessages, $errorCodes);
        }

        $response = $validationSubject['response'];

        if (isset($response[ResponseInterface::ERROR_MESSAGES])) {
            $isValid = false;
            foreach ($response[ResponseInterface::ERROR_MESSAGES] as $error) {
                $errorCodes[] = $error[ResponseInterface::ERROR_MESSAGE_CODE];
                $errorMessages[] = "{$error[ResponseInterface::ERROR_MESSAGE_DESCRIPTION]} ({$error[ResponseInterface::ERROR_MESSAGE_PARAMETER_NAME]})";
            }
        }

        $charge = isset($response[ResponseInterface::CHARGES]) ? $response[ResponseInterface::CHARGES][0] : [];
        $status = isset($charge[ResponseInterface::CHARGE_STATUS]) ? $charge[ResponseInterface::CHARGE_STATUS] : '';
        if (in_array($status, $this->responseErrorStatus)) {
            $isValid = false;
            $errorCodes[] = $charge[ResponseInterface::PAYMENT_RESPONSE][ResponseInterface::PAYMENT_RESPONSE_CODE];
            $errorMessages[] = $charge[ResponseInterface::PAYMENT_RESPONSE][ResponseInterface::PAYMENT_RESPONSE_MESSAGE];
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
            $errorMessagesWithCodes = array_combine($errorCodes, $errorMessages);
            $data = [
                "Response Error for order #{$orderIncrementId}" => $errorMessagesWithCodes
            ];
            $this->logger->debug($data,null,true);
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
