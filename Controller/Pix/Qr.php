<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Controller\Pix;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use RicardoMartins\PagBank\Model\Pix\QrImage;

/**
 * Streams the stored PIX QR with a browser-safe Content-Type.
 */
class Qr implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly QrImage $qrImage,
        private readonly RawFactory $rawFactory
    ) {
    }

    public function execute(): ResultInterface
    {
        $result = $this->rawFactory->create();
        $orderId = (int) $this->request->getParam('order_id');
        $code = (string) $this->request->getParam('code');

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            return $result->setHttpResponseCode(404);
        }

        $protect = (string) $order->getProtectCode();
        if ($code === '' || $protect === '' || !hash_equals($protect, $code)) {
            return $result->setHttpResponseCode(403);
        }

        $payment = $order->getPayment();
        $remote = $payment ? (string) $payment->getAdditionalInformation('payment_link_qrcode') : '';
        $fetched = $remote !== '' ? $this->qrImage->fetch($remote) : null;
        if ($fetched === null) {
            return $result->setHttpResponseCode(404);
        }

        return $result
            ->setHeader('Content-Type', $fetched['content_type'], true)
            ->setHeader('Cache-Control', 'private, max-age=3600', true)
            ->setHeader('X-Content-Type-Options', 'nosniff', true)
            ->setContents($fetched['body']);
    }
}
