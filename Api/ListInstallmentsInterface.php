<?php

namespace RicardoMartins\PagBank\Api;

interface ListInstallmentsInterface
{
    /**
     * @param int $cartId
     * @param int $creditCardBin
     * @return mixed
     */
    public function execute(int $cartId, int $creditCardBin);
}
