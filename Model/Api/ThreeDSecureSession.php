<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Laminas\Http\Request;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\Transfer;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;
use RicardoMartins\PagBank\Api\Connect\ThreeDSecureSessionInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;

class ThreeDSecureSession implements ThreeDSecureSessionInterface
{
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly GeneralClient $generalClient,
        private readonly Config $config,
        private readonly ConfigCc $configCc,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @return string
     */
    public function createThreeDSecureSession(): string
    {
        $response = [];
        $storeId = null;

        if (!$this->configCc->isThreeDSecureActive($storeId)) {
            return '';
        }

        try {
            $transferObject = $this->getTransferObject($storeId);
            $response = $this->generalClient->placeRequest($transferObject);
            if (!isset($response[ThreeDSecureSessionInterface::THREE3D_SECURE_SESSION])) {
                throw new LocalizedException(__('Error on create 3D Secure session'));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $response[ThreeDSecureSessionInterface::THREE3D_SECURE_SESSION];
    }

    /**
     * @param int|null $storeId
     * @return Transfer|TransferInterface
     */
    private function getTransferObject(?int $storeId = null): TransferInterface|Transfer
    {
        $connectKey = $this->config->getConnectKey($storeId);
        $uri = $this->config->get3DSecureSessionEndpoint($storeId);

        $headers = [
            "Authorization" => "Bearer {$connectKey}",
            "Content-Type" => "application/json"
        ];

        return $this->transferBuilder
            ->setHeaders($headers)
            ->setUri($uri)
            ->setMethod(Request::METHOD_POST)
            ->build();
    }
}
