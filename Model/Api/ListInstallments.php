<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use RicardoMartins\PagBank\Api\Connect\InstallmentsInterface;
use RicardoMartins\PagBank\Api\ListInstallmentsInterface;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;
use RicardoMartins\PagBank\Gateway\Http\TransferFactory\GetInterestsTransferFactory;
use RicardoMartins\PagBank\Model\Request\InstallmentsFactory;

class ListInstallments implements ListInstallmentsInterface
{
    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param ConfigCc $config
     * @param InstallmentsFactory $installmentsFactory
     * @param GetInterestsTransferFactory $getInterestsTransferFactory
     * @param GeneralClient $generalClient
     * @param Session $checkoutSession
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartTotalRepositoryInterface $cartTotalRepository,
        private readonly ConfigCc $config,
        private readonly InstallmentsFactory $installmentsFactory,
        private readonly GetInterestsTransferFactory $getInterestsTransferFactory,
        private readonly GeneralClient $generalClient,
        private readonly Session $checkoutSession
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(int $cartId, int $creditCardBin)
    {
        $quote = $totalRepository = null;

        try {
            $quote = $this->cartRepository->getActive($cartId);
            $totalRepository = $this->cartTotalRepository->get($cartId);
        } catch (\Exception $e) {
            return __('Quote not found');
        }

        $storeId = $quote->getStoreId();

        $interestAmount = $quote->getData('ricardomartins_pagbank_interest_amount');
        $grandTotalAmount = $totalRepository->getGrandTotal() - $interestAmount;

        $installmentsData = $this->installmentsFactory->create();
        $installmentsData->setPaymentMethods(InstallmentsInterface::PAYMENT_METHOD_TYPE_CC);
        $installmentsData->setValue((float) $grandTotalAmount);
        $installmentsData->setCreditCardBin($creditCardBin);

        if ($this->config->isEnabledInstallmentsLimit($storeId)) {
            $maxIntallments = (int) $this->config->getInstallmentsLimit($storeId);
            $installmentsData->setMaxInstallments($maxIntallments);
        }

        $maxIntallmentsNoInterest = $this->config->getMaxInstallmentsNoInterest($grandTotalAmount, $storeId);
        if (!is_null($maxIntallmentsNoInterest)) {
            $installmentsData->setMaxInstallmentsNoInterest($maxIntallmentsNoInterest);
        }

        $response = [];

        try {
            $transferObject = $this->getInterestsTransferFactory->create($installmentsData->getData());
            $response = $this->generalClient->placeRequest($transferObject);
            $installments = reset($response['payment_methods']['credit_card']);
            if (!isset($installments['installment_plans'])) {
                return [__('Error on get installments')];
            }
        } catch (\Exception $e) {
            return [__('Error on get installments')];
        }

        try {
            $this->checkoutSession->setInstallments($installments['installment_plans']);
        } catch (\Exception $e) {}

        return $installments['installment_plans'];
    }
}
