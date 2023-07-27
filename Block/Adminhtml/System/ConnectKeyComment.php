<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Adminhtml\System;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Config\Model\Config\CommentInterface;

class ConnectKeyComment extends AbstractBlock implements CommentInterface
{
    /**
     * @param $elementValue
     * @return string
     */
    public function getCommentText($elementValue): string
    {
        if (str_contains($elementValue, 'CONSANDBOX')) {
            return '⚠️ Você está usando o <strong>modo de testes</strong>. Veja <a href="https://dev.pagbank.uol.com.br/reference/simulador" target="_blank">documentação</a>.' .
                    '<br/>Para usar o modo de produção, altere suas credenciais.' .
                    '<br/>Lembre-se: pagamentos em Sandbox não aparecerão em seu painel, mesmo no ambiente Sandbox.';
        }

        return '';
    }
}
