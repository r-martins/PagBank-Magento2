<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;

class VindiThreeDsSetup extends AbstractVindi implements HttpPostActionInterface
{
    protected function dispatch(array $body): array
    {
        return $this->proxy()->threeDsSetup($body);
    }
}
