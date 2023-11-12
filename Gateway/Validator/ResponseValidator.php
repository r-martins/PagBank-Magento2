<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Validator;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order\Payment;
use RicardoMartins\PagBank\Api\Connect\ResponseInterface;
use RicardoMartins\PagBank\Plugin\Webapi\Controller\Rest;

class ResponseValidator extends AbstractValidator
{
    /**
     * @var array Response status that should be declined
     */
    private array $responseErrorStatus = [
        ResponseInterface::STATUS_CANCELED,
        ResponseInterface::STATUS_DECLINED
    ];

    /**
     * @var array Response error codes and messages
     */
    public array $errors = [
        '40001' =>	'Mandatory parameter. Some mandatory data was not provided.',
        '40002' =>	'Invalid parameter. Some data was reported in an invalid format or the data set did not meet all business requirements.',
        '40003' =>	'Invalid parameter. Some data was reported in an invalid format or the data set did not meet all business requirements.',
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
     * @throws CommandException
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $customMessage = null;
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
                $customMessage = $this->getDetailedErrorMessage(
                    $error[ResponseInterface::ERROR_MESSAGE_CODE],
                    $error[ResponseInterface::ERROR_MESSAGE_PARAMETER_NAME]
                );
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

        if ($customMessage) {
            throw new CommandException($customMessage);
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }

    /**
     * Returns a detailed error message for the given error code
     *
     * @param string $code
     * @param string $parameterName
     *
     * @return \Magento\Framework\Phrase|null
     */
    private function getDetailedErrorMessage(string $code, string $parameterName)
    {
        if (key_exists($code, $this->errors)) {
            $friendlyParameterName = $this->getFriendlyParameterName($parameterName);
            $message = __($this->errors[$code]);
            return __('[%1] %2 (%3)', $code, $message, $friendlyParameterName);
        }

        return null;
    }

    /**
     * Returns a friendly name for the parameter that is missing or invalid
     * @param string $parameterName
     *
     * @return string
     */
    private function getFriendlyParameterName(string $parameterName): string
    {
        return match ($parameterName) {
            'customer.tax_id' => __('CPF/CNPJ') . ' - ' . $parameterName,
            'customer.phones[0].number' => __('Telephone') . ' - ' . $parameterName,
            'charges[0].payment_method.boleto.due_date' => __('Payment slip due date') . ' - ' . $parameterName,
            default => $parameterName,
        };
    }
}
