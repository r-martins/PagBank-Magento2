<?php

namespace RicardoMartins\PagBank\Api;

interface GuestInterestInterface
{
    /**
     * @param string $cartId
     * @param int $installment
     * @param int $creditCardBin
     * @return mixed
     */
    public function execute(string $cartId, int $installment, int $creditCardBin);
}
