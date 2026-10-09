<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Adminhtml\System;

use RicardoMartins\PagBank\Model\System\Config\VindiAdminNotes;

class PixExpirationComment extends AbstractPartnerComment
{
    public function getCommentText($elementValue): string
    {
        $text = 'Tempo em minutos para expirar o pagamento PIX.<br>'
            . 'O tempo de validade do Pix deve ser menor ou igual ao tempo de vida do pagamento pendente.<br>'
            . 'Este valor pode ser alterado em: Vendas -> Vendas -> Configurações de Cron de Pedidos -> Tempo de Vida do Pedido de Pagamento Pendente.';

        if ($this->isVindi()) {
            $text .= '<br>' . VindiAdminNotes::visualExpiryNote('PIX');
        }

        return $text;
    }
}
