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
        if (!$elementValue) {
            return '';
        }

        $detector = \RicardoMartins\PagBank\Model\Partner\Detector::fromKey((string) $elementValue);
        if ($detector->isVindi() && $detector->isSandbox()) {
            return '⚠️ Você está usando uma Connect Key <strong>Vindi Sandbox</strong> (CONVDSANDBOX).';
        }

        if ($detector->isPagBank() && $detector->isSandbox()) {
            return '⚠️ Você está usando o <strong>modo de testes</strong>. Veja <a href="https://dev.pagbank.uol.com.br/reference/simulador" target="_blank">documentação</a>.' .
                    '<br/>Para usar o modo de produção, altere suas credenciais.' .
                    '<br/>Lembre-se: pagamentos em Sandbox não aparecerão em seu painel, mesmo no ambiente Sandbox.';
        }

        if ($detector->isPagBank() && $elementValue) {
            return 'As Connect Keys PagBank (CON…) deixam de processar pedidos novos em <strong>' .
                \RicardoMartins\PagBank\Model\Partner\Cutoff::SHORT .
                '</strong>. <a href="' . \RicardoMartins\PagBank\Model\Partner\Cutoff::MIGRATE_URL .
                '" target="_blank">Migrar para Vindi</a>.';
        }

        return '';
    }
}
