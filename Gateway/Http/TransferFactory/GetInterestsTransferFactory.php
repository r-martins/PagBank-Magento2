<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Http\TransferFactory;

use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Converter\Converter;

class GetInterestsTransferFactory implements TransferFactoryInterface
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
     * @throws ConverterException
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod(Request::METHOD_GET)
            ->setHeaders($this->config->getHeaders())
            ->setUri($this->config->getInterestEndpoint())
            ->setBody($request)
            ->build();
    }
}
