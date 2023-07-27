<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Http\TransferFactory;

use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Converter\Converter;

class GetTransactionInfoTransferFactory implements TransferFactoryInterface
{
    /**
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     */
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly Config $config
    ) {}

    /**
     * {@inheritdoc}
     */
    public function create(array $request)
    {
        $paymentId = $request['id'];
        $uri = $this->config->getPaymentInfoEndpoint();

        return $this->transferBuilder
            ->setMethod(Request::METHOD_GET)
            ->setHeaders($this->config->getHeaders())
            ->setUri(sprintf($uri, $paymentId))
            ->build();
    }
}
