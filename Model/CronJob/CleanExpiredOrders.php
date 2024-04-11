<?php

namespace RicardoMartins\PagBank\Model\CronJob;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoresConfig;

class CleanExpiredOrders extends \Magento\Sales\Model\CronJob\CleanExpiredOrders
{
    public function __construct(
        private OrderManagementInterface $orderManagement,
        StoresConfig $storesConfig,
        CollectionFactory $collectionFactory
    ){
        parent::__construct($storesConfig, $collectionFactory, $orderManagement);
    }

    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('sales/orders/delete_pending_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToFilter('store_id', $storeId);
            $orders->addFieldToFilter('status', Order::STATE_PENDING_PAYMENT);
            $orders->getSelect()->joinLeft(
                ['payment' => 'sales_order_payment'],
                'payment.parent_id = main_table.entity_id',
                ['payment_method' => 'payment.method']
            );

            $orders->getSelect()->where(
                new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `updated_at`)) >= ' . $lifetime * 60)
            )
                ->where('payment.method NOT LIKE \'ricardomartins_pagbank_boleto\'');;

            foreach ($orders->getAllIds() as $entityId) {
                $this->orderManagement->cancel((int) $entityId);
            }
        }
    }
}
