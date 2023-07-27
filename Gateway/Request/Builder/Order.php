<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Gateway\Request\Builder;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;

class Order implements BuilderInterface
{
    /**
     * The unique order identifier.
     * Receives the order increment id string.
     */
    public const REFERENCE_ID = 'reference_id';

    /**
     * The notification urls.
     * Receives an array of strings.
     */
    public const NOTIFICATION_URLS = 'notification_urls';

    /**
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config
    ) {}

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];
        $order = $paymentDataObject->getOrder();

        return [
            self::REFERENCE_ID => $order->getOrderIncrementId(),
            self::NOTIFICATION_URLS => $this->getNotificationUrls($order)
        ];
    }

    /**
     * @param $order
     * @return array
     * @throws NoSuchEntityException
     */
    private function getNotificationUrls($order): array
    {
        $hash = $this->config->getOrderHash($order->getOrderIncrementId());
        $storeId = $order->getStoreId();
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        return [
            $baseUrl . $this->config->getNotificationEndpoint() . '?hash=' . $hash
        ];
    }
}
