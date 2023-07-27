<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Converter;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;

class JsonToArray implements ConverterInterface
{
    public function __construct(
      private SerializerInterface $serializer
    ) {}

    /**
     * @inheritDoc
     */
    public function convert($response): float|array|bool|int|string|null
    {
        return $this->serializer->unserialize($response);
    }
}
