<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Converter;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;

class ArrayToJson implements ConverterInterface
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    /**
     * @inheritDoc
     */
    public function convert($response): bool|array|string
    {
        return $this->serializer->serialize($response);
    }
}
