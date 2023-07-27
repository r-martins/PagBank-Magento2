<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Controller\Notifications;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;

class Index implements CsrfAwareActionInterface, HttpPostActionInterface
{
    public function __construct(
        private readonly ResultFactory $resultFactory,
        private readonly RequestInterface $request,
        private readonly SerializerInterface $serializer,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly OrderResourceInterface $orderResource,
        private readonly OrderRepository $orderRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly Config $config,
        private readonly Logger $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->request->isPost()) {
            $result->setHttpResponseCode(404);
            return $result;
        }
        $content = $this->request->getContent();
        $hash = $this->request->getParam('hash');
        $content = $this->serializer->unserialize($content);

        $pagbankPaymentId = $content['id'];
        $orderIncrementId = $content['reference_id'];

        $this->logger->debug(['message' => __("Received PagBank Notification for order {$orderIncrementId}")]);

        if ($hash !== $this->config->getOrderHash($orderIncrementId)) {
            $message = __("Invalid hash for order {$orderIncrementId}");
            $this->logger->debug(['message' => $message, 'hash' => $hash]);
            $result->setHttpResponseCode(500);
            $result->setData(['message' => $message]);
            return $result;
        }

        $order = $this->getOrderByTransaction($pagbankPaymentId);

        if (!$order) {
            $message = __("Order {$orderIncrementId} not found");
            $this->logger->debug(['message' => $message]);
            $result->setHttpResponseCode(500);
            $result->setData(['message' => $message]);
            return $result;
        }

        try {
            $payment = $order->getPayment();
            $payment->update(true);
            $this->orderResource->save($order);
        } catch (\Exception $e) {
            $this->logger->debug(['message' => $e->getMessage()]);
            $result->setHttpResponseCode(500);
            return $result;
        }

        $result->setHttpResponseCode(200);

        return $result;
    }

    /**
     * @param string $transactionId
     * @return OrderInterface|null
     */
    public function getOrderByTransaction(string $transactionId)
    {
        $order = null;
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('txn_id', $transactionId)
            ->addFilter(TransactionInterface::TXN_TYPE, TransactionInterface::TYPE_ORDER)
            ->create();

        try {
            $transaction = $this->transactionRepository->getList($searchCriteria)->getFirstItem();
            $order = $this->orderRepository->get($transaction->getOrderId());
        } catch (\Exception $e) {
            $this->logger->debug(['message' => $e->getMessage()]);
            return null;
        }

        return $order;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
