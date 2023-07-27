<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Http\Client;

use Laminas\Http\ClientFactory;
use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class GeneralClient implements ClientInterface
{
    public function __construct(
        private readonly ClientFactory $httpClientFactory,
        private readonly Logger $logger,
        private readonly ?ConverterInterface $converter = null
    ) {}

    /**
     * Custom method to place general requests to gateway.
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws ConverterException
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $logRequest = $transferObject->getMethod() !== Request::METHOD_GET;
        $result = [];
        $response = null;

        if ($logRequest) {
            $log = [
                'request_uri' => $transferObject->getUri(),
                'request' => $this->converter && is_string($transferObject->getBody())
                    ? $this->converter->convert($transferObject->getBody())
                    : $transferObject->getBody()
            ];
        }

        $client = $this->httpClientFactory->create();

        try {
            $client->setOptions($transferObject->getClientConfig());
            $client->setMethod($transferObject->getMethod());
            $client->setHeaders($transferObject->getHeaders());
            $client->setUri($transferObject->getUri());

            switch ($transferObject->getMethod()) {
                case Request::METHOD_GET:
                    $client->setParameterGet($transferObject->getBody());
                    break;
                default:
                    $client->setRawBody($transferObject->getBody());
                    break;
            }

            $response = $client->send();

            $result = $this->converter
                ? $this->converter->convert($response->getBody())
                : [$response->getBody()];

        } catch (\Exception $e) {
            $this->logger->debug(['error' => $e->getMessage()]);
        } finally {
            if ($logRequest) {
                $log['response'] = $result;
                $this->logger->debug($log);
            }
        }

        return $result;
    }
}
