<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use RicardoMartins\PagBank\Api\Connect\InstallmentsInterface;
use RicardoMartins\PagBank\Api\ListInstallmentsInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;
use RicardoMartins\PagBank\Gateway\Http\TransferFactory\GetInterestsTransferFactory;
use RicardoMartins\PagBank\Model\Request\InstallmentsFactory;

class ListInstallments implements ListInstallmentsInterface
{
    /**
     * @param CartRepositoryInterface      $cartRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param ConfigCc                     $configCc
     * @param InstallmentsFactory          $installmentsFactory
     * @param GetInterestsTransferFactory  $getInterestsTransferFactory
     * @param GeneralClient                $generalClient
     * @param Session                      $checkoutSession
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartTotalRepositoryInterface $cartTotalRepository,
        private readonly Config $config,
        private readonly ConfigCc $configCc,
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

        if ($this->config->isSandbox($storeId)) {
            $installmentsData->setCreditCardBin(555566); // Test bin (as most bins are not recognized in Sandbox)
        }

        if ($this->configCc->isEnabledInstallmentsLimit($storeId)) {
            $maxIntallments = $this->configCc->getInstallmentsLimit($storeId);
            $installmentsData->setMaxInstallments($maxIntallments);
        }

        $maxIntallmentsNoInterest = $this->configCc->getMaxInstallmentsNoInterest($grandTotalAmount, $storeId);
        if (!is_null($maxIntallmentsNoInterest)) {
            $installmentsData->setMaxInstallmentsNoInterest($maxIntallmentsNoInterest);
        }

        $response = [];

        try {
            $transferObject = $this->getInterestsTransferFactory->create([
                'body' => $installmentsData->getData(),
                'store_id' => $storeId
            ]);
            $response = $this->generalClient->placeRequest($transferObject);
            if (!isset($response['payment_methods']['credit_card'])) {
                return [];
            }

            $installments = reset($response['payment_methods']['credit_card']);
            if (!isset($installments['installment_plans'])) {
                return [];
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
