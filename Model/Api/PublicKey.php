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
use RicardoMartins\PagBank\Gateway\Converter\Converter;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;

class PublicKey implements PublicKeyInterface
{
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly Converter $converter,
        private readonly GeneralClient $generalClient,
        private readonly WriterInterface $configWriter
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
            $transferObject = $this->getPublicKeyTransferObject($headers, $request, $connectKey);
            $response = $this->generalClient->placeRequest($transferObject);
            if (!isset($response[PublicKeyInterface::PUBLIC_KEY]) || empty($response[PublicKeyInterface::PUBLIC_KEY])) {
                $error = array_key_exists(PublicKeyInterface::RESPONSE_ERROR, $response) ? $response[PublicKeyInterface::RESPONSE_ERROR] : '';
                throw new LocalizedException(__($error));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error on create public key: %1', $e->getMessage()));
        }

        return $response[PublicKeyInterface::PUBLIC_KEY];
    }

    /**
     * @param string $publicKey
     * @return void
     */
    public function savePublicKey(string $publicKey): void
    {
        $this->configWriter->save(PublicKeyInterface::PUBLIC_KEY_CONFIG_PATH, $publicKey);
    }

    /**
     * @param array $headers
     * @param array $request
     * @param string $connectKey
     * @return Transfer|TransferInterface
     * @throws ConverterException
     */
    private function getPublicKeyTransferObject(array $headers, array $request, string $connectKey): TransferInterface|Transfer
    {
        $uri = ConnectInterface::WS_ENDPOINT_PUBLIC_KEY;
        if ($this->isSandbox($connectKey)) {
            $uri .= '?' . ConnectInterface::SANDBOX_PARAM;
        }

        return $this->transferBuilder
            ->setHeaders($headers)
            ->setUri($uri)
            ->setMethod(Request::METHOD_POST)
            ->setBody($this->converter->convert($request))
            ->build();
    }

    /**
     * @param string $connectKey
     * @return bool
     */
    private function isSandbox(string $connectKey): bool
    {
        if (str_contains($connectKey, ConnectInterface::SANDBOX_PREFIX)) {
            return true;
        }
        return false;
    }
}
