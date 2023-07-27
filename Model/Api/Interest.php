<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use RicardoMartins\PagBank\Api\InterestInterface;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;
use RicardoMartins\PagBank\Gateway\Http\TransferFactory\GetInterestsTransferFactory;
use RicardoMartins\PagBank\Model\Request\InstallmentsFactory;

class Interest implements InterestInterface
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
    public function execute(int $cartId, int $installment, int $creditCardBin)
    {
        $quote = $totalRepository = $installmentsList = null;

        try {
            $quote = $this->cartRepository->getActive($cartId);
            $totalRepository = $this->cartTotalRepository->get($cartId);
        } catch (\Exception $e) {
            return __('Quote not found');
        }

        try {
            $installmentsList = $this->checkoutSession->getInstallments();
        } catch (\Exception $e) {}

        if (!$installmentsList) {
            try {
                $storeId = $quote->getStoreId();
                $grandTotalAmount = $totalRepository->getGrandTotal();

                $installmentsData = $this->installmentsFactory->create();
                $installmentsData->setAmount((float) $grandTotalAmount);
                $installmentsData->setCreditCardBin($creditCardBin);

                if ($this->config->isEnabledInstallmentsLimit($storeId)) {
                    $maxIntallments = $this->config->getInstallmentsLimit($storeId);
                    $installmentsData->setMaxInstallments($maxIntallments);
                }

                $maxIntallmentsNoInterest = $this->config->getMaxInstallmentsNoInterest($grandTotalAmount, $storeId);
                $installmentsData->setMaxInstallmentsNoInterest($maxIntallmentsNoInterest);

                $transferObject = $this->getInterestsTransferFactory->create($installmentsData->getData());
                $response = $this->generalClient->placeRequest($transferObject);
                $installmentsList = reset($response['payment_methods']['credit_card']);
            } catch (\Exception $e) {
                return false;
            }
        }

        $interestAmount = 0;
        foreach ($installmentsList as $installmentItem) {
            if ($installmentItem['installments'] == $installment && !$installmentItem['interest_free']) {
                $interestAmount = $installmentItem['amount']['fees']['buyer']['interest']['total'];
                $interestAmount = $interestAmount / 100;
                break;
            }
        }

        try {
            $quote->setData('ricardomartins_pagbank_interest_amount', $interestAmount);
            $quote->setData('ricardomartins_pagbank_interest_base_amount', $interestAmount);
            $this->cartRepository->save($quote);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
