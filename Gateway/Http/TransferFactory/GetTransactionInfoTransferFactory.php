<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Http\TransferFactory;

use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Converter\Converter;

class GetTransactionInfoTransferFactory implements TransferFactoryInterface
{
    /**
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {}

    /**
     * {@inheritdoc}
     */
    public function create(array $request)
    {
        $paymentId = $request['id'];
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
