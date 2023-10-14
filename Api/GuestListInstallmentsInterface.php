<?php

namespace RicardoMartins\PagBank\Api;

interface GuestListInstallmentsInterface
{
    /**
     * @param string $cartId
     * @param int $creditCardBin
     * @return mixed
     */
    public function execute(string $cartId, int $creditCardBin);
}
