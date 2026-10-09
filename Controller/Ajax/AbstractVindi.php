<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Controller\Ajax;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use RicardoMartins\PagBank\Model\Vindi\Proxy;

abstract class AbstractVindi implements CsrfAwareActionInterface
{
    public function __construct(
        private readonly JsonFactory $jsonFactory,
        private readonly RequestInterface $request,
        private readonly Proxy $proxy
    ) {
    }

    /**
     * @param array $body
     * @return array
     * @throws LocalizedException
     */
    abstract protected function dispatch(array $body): array;

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        try {
            $raw = (string) $this->request->getContent();
            $body = $raw !== '' ? json_decode($raw, true) : $this->request->getParams();
            if (!is_array($body)) {
                $body = [];
            }
            $result->setData([
                'success' => true,
                'data' => $this->dispatch($body),
            ]);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(400);
            $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    protected function proxy(): Proxy
    {
        return $this->proxy;
    }
}
