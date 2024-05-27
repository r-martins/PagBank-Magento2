<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder\Charges;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use RicardoMartins\PagBank\Api\Connect\AmountInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\ChargeInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\HolderInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethodInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\AuthenticationMethodInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\AuthenticationMethodInterfaceFactory;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\CardInterfaceFactory;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Model\Request\ChargeFactory;

class CreditCard implements BuilderInterface
{
    /**
     * Represents all data available on a charge.
     * Receives an array of charges.
     */
    public const CHARGES = 'charges';

    public function __construct(
        private readonly ChargeInterfaceFactory $chargeFactory,
        private readonly AmountInterfaceFactory $amountFactory,
        private readonly CardInterfaceFactory $cardFactory,
        private readonly AuthenticationMethodInterfaceFactory $authenticationMethodFactory,
        private readonly HolderInterfaceFactory $holderFactory,
        private readonly PaymentMethodInterfaceFactory $paymentMethodFactory,
        private readonly ConfigCc $config
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $order = $paymentDataObject->getOrder();

        /** @var Order $orderModel */
        $orderModel = $payment->getOrder();

        $result = [];

        $charges = $this->chargeFactory->create();
        $charges->setReferenceId($orderModel->getIncrementId());

        $amount = $this->amountFactory->create();
        $amount->setValue($orderModel->getGrandTotal());
        $amount->setCurrency($order->getCurrencyCode());

        $charges->setAmount($amount->getData());

        $holder = $this->holderFactory->create();
        $holder->setName($payment->getData('cc_owner'));

        $card = $this->cardFactory->create();
        $card->setHolder($holder->getData());
        $card->setEncrypted($payment->getAdditionalInformation('cc_number_encrypted'));

        $paymentMethod = $this->paymentMethodFactory->create();
        $paymentMethod->setType(PaymentMethodInterface::TYPE_CREDIT_CARD);
        $paymentMethod->setInstallments((int) $payment->getAdditionalInformation('cc_installments'));
        $paymentMethod->setCapture(true);
        $paymentMethod->setCard($card->getData());

        $softDescriptor = $this->config->getSoftDescriptor($orderModel->getStoreId());
        $paymentMethod->setSoftDescriptor($softDescriptor);

        if ($this->config->isThreeDSecureActive()) {
            $threeDsId = $payment->getAdditionalInformation('threed_secure_id');
            $allowContinue = $this->config->isThreeDSecureAllowContinue($orderModel->getStoreId());
            $authenticationMethod = $this->getAuthenticationMethodData($threeDsId, $allowContinue);

            if ($authenticationMethod->getId()) {
                $paymentMethod->setAuthenticationMethod($authenticationMethod->getData());
            }
        }

        $charges->setPaymentMethod($paymentMethod->getData());

        $result[self::CHARGES][] = $charges->getData();

        return $result;
    }

    /**
     * @param string|null $threeDsId
     * @param bool $allowContinue
     * @return null|AuthenticationMethodInterface
     */
    private function getAuthenticationMethodData(?string $threeDsId, bool $allowContinue): ?AuthenticationMethodInterface
    {
        if (!$threeDsId && !$allowContinue) {
            return null;
        }

        $authenticationMethod = $this->authenticationMethodFactory->create();

        if ($threeDsId) {
            $authenticationMethod->setType(AuthenticationMethodInterface::TYPE_THREEDS);
            $authenticationMethod->setId($threeDsId);
        }

        return $authenticationMethod;
    }
}
