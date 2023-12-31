<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\PaymentMethod\Boleto;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\InstructionLinesInterface;

class InstructionLines extends DataObject implements InstructionLinesInterface
{
    /**
     * @return string
     */
    public function getLineOne(): string
    {
        return $this->getData(InstructionLinesInterface::INSTRUCTION_LINE_ONE);
    }

    /**
     * @param string|null $lineOne
     * @return InstructionLinesInterface
     */
    public function setLineOne(?string $lineOne): InstructionLinesInterface
    {
        $lineOne = $lineOne ? substr($lineOne, 0, 75): null;
        return $this->setData(InstructionLinesInterface::INSTRUCTION_LINE_ONE, $lineOne);
    }

    /**
     * @return string
     */
    public function getLineTwo(): string
    {
        return $this->getData(InstructionLinesInterface::INSTRUCTION_LINE_TWO);
    }

    /**
     * @param string|null $lineTwo
     * @return InstructionLinesInterface
     */
    public function setLineTwo(?string $lineTwo): InstructionLinesInterface
    {
        $lineTwo = $lineTwo ? substr($lineTwo, 0, 75) : null;
        return $this->setData(InstructionLinesInterface::INSTRUCTION_LINE_TWO, $lineTwo);
    }
}
