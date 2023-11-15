<?php

declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use RicardoMartins\PagBank\Api\Connect\ItemInterfaceFactory;

/**
 * Class Items
 */
class Items implements BuilderInterface
{
    /**
     * Contains the information of the items included in the order.
     * Receives an array of items.
     */
    public const ITEMS = 'items';

    /**
     * @param ItemInterfaceFactory $itemFactory
     */
    public function __construct(
        private ItemInterfaceFactory $itemFactory
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $order = $paymentDataObject->getOrder();
        $orderItems = $order->getItems();

        $result = $items = [];

        /** @var OrderItemInterface $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }

            $price = $orderItem->getPrice();
            if ($price == 0) {
                continue;
            }

            $item = $this->itemFactory->create();
            $item->setReferenceId($orderItem->getSku());
            $item->setName($orderItem->getName());
            $item->setQuantity((int) $orderItem->getQtyOrdered());
            $item->setUnitAmount($orderItem->getPrice());
            $items[] = $item->getData();
        }

        if (empty($items)) {
            return $result;
        }

        $result[self::ITEMS] = $items;

        return $result;
    }
}
