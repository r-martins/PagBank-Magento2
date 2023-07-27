<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Request\PaymentMethod;

use Magento\Framework\DataObject;
use RicardoMartins\PagBank\Api\Connect\HolderInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\InstructionLinesInterface;
use RicardoMartins\PagBank\Api\Connect\PaymentMethod\BoletoInterface;

class Boleto extends DataObject implements BoletoInterface
{
    /**
     * @return string
     */
    public function getDueDate(): string
    {
        return $this->getData(BoletoInterface::DUE_DATE);
    }

    /**
     * @param string $dueDate
     * @return BoletoInterface
     */
    public function setDueDate(string $dueDate): BoletoInterface
    {
        return $this->setData(BoletoInterface::DUE_DATE, $dueDate);
    }

    /**
     * @return InstructionLinesInterface[]
     */
    public function getInstructionLines(): array
    {
        return $this->getData(BoletoInterface::INSTRUCTION_LINES);
    }

    /**
     * @param InstructionLinesInterface[] $instructionLines
     * @return Boleto
     */
    public function setInstructionLines(array $instructionLines): BoletoInterface
    {
        return $this->setData(BoletoInterface::INSTRUCTION_LINES, $instructionLines);
    }

    /**
     * @return HolderInterface[]
     */
    public function getHolder(): array
    {
        return $this->getData(BoletoInterface::HOLDER);
    }

    /**
     * @param HolderInterface[] $holder
     * @return BoletoInterface
     */
    public function setHolder(array $holder): BoletoInterface
    {
        return $this->setData(BoletoInterface::HOLDER, $holder);
    }
}
