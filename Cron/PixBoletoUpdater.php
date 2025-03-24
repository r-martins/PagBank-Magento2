<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Cron;

use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Psr\Log\LoggerInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;

class PixBoletoUpdater
{
    public function __construct(
        private CollectionFactory $orderCollectionFactory,
        private OrderResourceInterface $orderResource,
        private readonly Config $config,
        private LoggerInterface $logger
    ) {
    }

    public function execute()
    {
        if (!$this->config->updatePixAndBoletoOrdersCron()) {
            return;
        }

        $orderCollection = $this->getPagBankBoletoAndPixOrders();

        foreach ($orderCollection->getItems() as $order) {
            try {
                $payment = $order->getPayment();
                $payment->update(true);
                $this->orderResource->save($order);
            } catch (\Exception $e) {
                $this->logger->error('Error process order ' . $order->getIncrementId() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * @return Collection
     */
    private function getPagBankBoletoAndPixOrders(): Collection
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', ['nin' => ['processing', 'canceled', 'closed', 'complete']])
            ->addFieldToFilter('state', ['nin' => ['processing', 'canceled', 'closed', 'complete']]);

        $collection->getSelect()
            ->join(
                ['payment' => $collection->getTable('sales_order_payment')],
                'main_table.entity_id = payment.parent_id',
                ['method']
            )->where('payment.method IN (?)', [ConfigBoleto::METHOD_CODE, ConfigQrCode::METHOD_CODE]);

        return $collection->setOrder('created_at', 'ASC');
    }
}
