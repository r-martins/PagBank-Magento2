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
use Magento\Store\Model\StoreManagerInterface;
use RicardoMartins\PagBank\Api\Connect\ConnectInterface;
use RicardoMartins\PagBank\Api\Connect\ConsultOrderInterface;
use RicardoMartins\PagBank\Api\Connect\PublicKeyInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Converter\Converter;
use RicardoMartins\PagBank\Gateway\Http\Client\GeneralClient;

/**
 * Class ConsultOrder - Consult order details on PagBank
 * @see https://dev.pagbank.uol.com.br/reference/consultar-pedido
 */
class ConsultOrder implements ConsultOrderInterface
{
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly StoreManagerInterface $storeManager,
        private readonly GeneralClient $generalClient,
        private readonly Config $config
    ) {}


    public function execute(string $pagBankOrderId)
    {
        $response = [];

        try {
            $transferObject = $this->getTransferObject($pagBankOrderId);
            $response = $this->generalClient->placeRequest($transferObject);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error on create public key: %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $paymentId
     * @return TransferInterface|Transfer
     */
    private function getTransferObject($paymentId): TransferInterface|Transfer
    {
        $storeId = null;

        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (\Exception $e) {}

        $uri = $this->config->getPaymentInfoEndpoint($storeId);

        return $this->transferBuilder
            ->setMethod(Request::METHOD_GET)
            ->setHeaders($this->config->getHeaders($storeId))
            ->setUri(sprintf($uri, $paymentId))
            ->build();
    }
}
