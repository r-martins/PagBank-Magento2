<?php

namespace RicardoMartins\PagBank\Api;

interface InterestInterface
{
    /**
     * @param int $cartId
     * @param int $installment
     * @param int $creditCardBin
     * @return mixed
     */
    public function execute(int $cartId, int $installment, int $creditCardBin);
}
