<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Order\Status;

use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Psr\Log\LoggerInterface;

class History
{
    public function __construct(
        private readonly OrderStatusHistoryRepositoryInterface $statusHistoryRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @param $order
     * @param $comment
     * @return bool
     */
    public function addCommentToStatusHistory($order,$comment): bool
    {
        $orderComment = $order->addCommentToStatusHistory($comment);

        try {
            $this->statusHistoryRepository->save($orderComment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }

        return true;
    }
}
