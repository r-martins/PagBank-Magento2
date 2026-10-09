<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Pix;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Vindi serves PIX QR SVGs from CloudFront without a Content-Type.
 * Browsers refuse that in img, so the store proxies the file with a MIME type.
 */
class QrImage
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly Curl $curl
    ) {
    }

    public function needsProxy(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
        if (preg_match('/\.svgz?$/i', $path)) {
            return true;
        }

        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));

        return $host !== '' && (
            str_contains($host, 'cloudfront.net')
            || str_contains($host, 'vindi.com.br')
            || str_contains($host, 'yapay.com.br')
        );
    }

    public function displayUrl(?Order $order, string $remote): string
    {
        if ($remote === '' || !$this->needsProxy($remote) || !$order || !$order->getId() || !$order->getProtectCode()) {
            return $remote;
        }

        return $this->storeManager->getStore($order->getStoreId())->getUrl('pagbank/pix/qr', [
            'order_id' => (int) $order->getId(),
            'code' => (string) $order->getProtectCode(),
            '_nosid' => true,
        ]);
    }

    /**
     * @return array{body: string, content_type: string}|null
     */
    public function fetch(string $url): ?array
    {
        if (!$this->needsProxy($url) || !$this->isHttps($url)) {
            return null;
        }

        $this->curl->setTimeout(30);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, false);
        $this->curl->get($url);
        $status = $this->curl->getStatus();
        $body = (string) $this->curl->getBody();
        if ($status < 200 || $status >= 300 || $body === '') {
            return null;
        }

        $headers = $this->curl->getHeaders();
        $remoteType = $headers['content-type'] ?? $headers['Content-Type'] ?? '';

        return [
            'body' => $body,
            'content_type' => $this->detectContentType($url, $body, is_array($remoteType) ? (string) ($remoteType[0] ?? '') : (string) $remoteType),
        ];
    }

    private function isHttps(string $url): bool
    {
        return strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }

    private function detectContentType(string $url, string $body, string $remoteContentType): string
    {
        $trimmed = ltrim($body);
        if (str_starts_with($trimmed, '<?xml') || str_starts_with($trimmed, '<svg')) {
            return 'image/svg+xml';
        }
        if (str_starts_with($body, "\x89PNG")) {
            return 'image/png';
        }
        if (str_starts_with($body, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }

        $remote = strtolower(trim($remoteContentType));
        if (str_starts_with($remote, 'image/')) {
            $mime = strtok($remote, ';');

            return is_string($mime) && $mime !== '' ? $mime : 'image/png';
        }

        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
        if (preg_match('/\.svgz?$/i', $path)) {
            return 'image/svg+xml';
        }
        if (preg_match('/\.jpe?g$/i', $path)) {
            return 'image/jpeg';
        }

        return 'image/png';
    }
}
