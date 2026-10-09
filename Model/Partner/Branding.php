<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Partner;

use RicardoMartins\PagBank\Api\Connect\ConnectInterface;

/**
 * Display names by partner (settings key vs payment additional information).
 */
class Branding
{
    public function __construct(private readonly ?Detector $detector = null)
    {
    }

    public function withDetector(Detector $detector): self
    {
        return new self($detector);
    }

    public static function forOrderPartner(string $partner): self
    {
        $fake = $partner === Partner::VINDI ? 'CONVD' : 'CON';

        return new self(Detector::fromKey($fake));
    }

    public function productName(): string
    {
        return $this->detector()->isVindi() ? 'Vindi' : 'PagBank';
    }

    public function methodTitlePrefix(): string
    {
        return $this->detector()->isVindi() ? 'via Vindi' : 'via PagBank';
    }

    public function viewChargeLabel(): string
    {
        return $this->detector()->isVindi() ? 'View on Vindi' : 'View on PagBank';
    }

    /**
     * Admin portal URL for a charge, or null when not linkable (PagBank sandbox).
     */
    public function adminChargeUrl(string $chargeId, bool $isSandbox): ?string
    {
        $chargeId = trim($chargeId);
        if ($chargeId === '') {
            return null;
        }

        if ($this->detector()->isVindi()) {
            $host = $isSandbox ? 'sandbox-app.vindi.com.br' : 'app.vindi.com.br';

            return 'https://' . $host . '/admin/charges/' . rawurlencode($chargeId);
        }

        if ($isSandbox) {
            return null;
        }

        $transaction = str_replace('CHAR_', '', $chargeId);

        return ConnectInterface::PAGBANK_TRANSACTION_DETAILS_URL . rawurlencode($transaction);
    }

    private function detector(): Detector
    {
        return $this->detector ?? Detector::fromKey('');
    }
}
