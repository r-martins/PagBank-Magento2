<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Api;

use Laminas\Http\Request;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\Transfer;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use RicardoMartins\PagBank\Api\Connect\ConnectInterface;
use RicardoMartins\PagBank\Api\Connect\PublicKeyInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Converter\Converter;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;
use RicardoMartins\PagBank\Model\Partner\Detector;

class PublicKey implements PublicKeyInterface
{
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly Converter $converter,
        private readonly GeneralClient $generalClient,
        private readonly WriterInterface $configWriter,
        private readonly Config $config
    ) {}

    /**
     * @param string $connectKey
     * @return string
     * @throws LocalizedException
     */
    public function createPublicKey(string $connectKey): string
    {
        $response = [];

        $headers = [
            "Authorization" => "Bearer {$connectKey}",
            "Content-Type" => "application/json"
        ];

        $request = [
            PublicKeyInterface::TYPE => PublicKeyInterface::TYPE_CARD
        ];

        try {
            if (Detector::fromKey($connectKey)->isVindi()) {
                return $this->fetchVindiPublicKey($headers);
            }

            $transferObject = $this->getPublicKeyTransferObject($headers, $request);
            $response = $this->generalClient->placeRequest($transferObject);
            if (!isset($response[PublicKeyInterface::PUBLIC_KEY]) || empty($response[PublicKeyInterface::PUBLIC_KEY])) {
                $error = array_key_exists(PublicKeyInterface::RESPONSE_ERROR, $response) ? $response[PublicKeyInterface::RESPONSE_ERROR] : '';
                throw new LocalizedException(__($error));
            }
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error on create public key: %1', $e->getMessage()));
        }

        return $response[PublicKeyInterface::PUBLIC_KEY];
    }

    /**
     * @param string $publicKey
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function savePublicKey(string $publicKey, string $scope, int $scopeId): void
    {
        $this->configWriter->save(PublicKeyInterface::PUBLIC_KEY_CONFIG_PATH, $publicKey, $scope, $scopeId);
    }

    /**
     * @param array $headers
     * @param array $request
     * @return Transfer|TransferInterface
     * @throws ConverterException
     */
    private function getPublicKeyTransferObject(array $headers, array $request): TransferInterface|Transfer
    {
        return $this->transferBuilder
            ->setHeaders($headers)
            ->setUri(ConnectInterface::WS_ENDPOINT_PUBLIC_KEY)
            ->setMethod(Request::METHOD_POST)
            ->setBody($this->converter->convert($request))
            ->build();
    }

    /**
     * Vindi does not issue a PagBank public key. Persist the public API key from tokenize/config.
     *
     * @param array $headers
     * @throws LocalizedException
     */
    private function fetchVindiPublicKey(array $headers): string
    {
        $transferObject = $this->transferBuilder
            ->setHeaders($headers)
            ->setUri($this->config->getTokenizeConfigEndpoint())
            ->setMethod(Request::METHOD_GET)
            ->setBody([])
            ->build();
        $response = $this->generalClient->placeRequest($transferObject);
        $publicKey = (string) ($response['public_api_key'] ?? $response['public_key'] ?? $response['publicKey'] ?? '');
        if ($publicKey === '') {
            throw new LocalizedException(__('Error on create public key: %1', __('tokenize/config did not return a public key.')));
        }

        return $publicKey;
    }
}
