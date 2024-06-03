<?php

namespace RicardoMartins\PagBank\Api\Connect;

/**
 * Interface ConsultOrderInterface - Consult order on PagBank.
 * @see https://dev.pagbank.uol.com.br/reference/consultar-pedido
 */
interface ConsultOrderInterface
{
    /**
     * @param string $pagBankOrderId
     * @return mixed
     */
    public function execute(string $pagBankOrderId);
}
