<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Challenge return page. The issuer POSTs TransactionId here without a Magento form_key.
 * Posts that id to the checkout window so the 3DS flow can continue.
 */
class VindiThreeDsReturn implements
    CsrfAwareActionInterface,
    HttpGetActionInterface,
    HttpPostActionInterface
{
    public function __construct(
        private readonly RawFactory $rawFactory,
        private readonly RequestInterface $request
    ) {
    }

    public function execute(): ResultInterface
    {
        $txid = (string) $this->request->getParam('TransactionId', '');
        $jsonId = json_encode($txid, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>3DS</title></head><body>'
            . '<script>'
            . 'try{if(window.parent&&window.parent!==window){'
            . 'window.parent.postMessage({source:"rm-pagbank-vindi-3ds",TransactionId:' . $jsonId . '},"*");'
            . '}}catch(e){}'
            . '</script>'
            . '<p>Autenticação concluída. Você pode fechar esta janela.</p>'
            . '</body></html>';

        return $this->rawFactory->create()
            ->setHeader('Content-Type', 'text/html; charset=utf-8', true)
            ->setHeader('X-Frame-Options', 'SAMEORIGIN', true)
            ->setContents($html);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
