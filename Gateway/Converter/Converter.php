<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Converter;

use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;

class Converter
{
    public function __construct(
        private ConverterInterface $converter
    ) {}

    /**
     * @param array $request
     * @return array|string
     * @throws ConverterException
     */
    public function convert(array $request): array|string
    {
        return $this->converter->convert($request);
    }
}
