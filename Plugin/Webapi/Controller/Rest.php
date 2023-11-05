<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Plugin\Webapi\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Webapi\Controller\Rest as MagentoRest;

class Rest
{
    /** @var bool */
    private bool $clearHeader = false;

    /**
     * @param MagentoRest $subject
     * @param ResponseInterface $result
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function afterDispatch(
        MagentoRest $subject,
        ResponseInterface $result,
        RequestInterface $request
    ) {
        if ($this->shouldClearHeader()) {
            $result->clearHeader('errorRedirectAction');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function shouldClearHeader(): bool
    {
        return $this->clearHeader;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setClearHeader(bool $value): void
    {
        $this->clearHeader = $value;
    }
}
