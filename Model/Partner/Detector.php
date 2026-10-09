<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Partner;

/**
 * Detects payment partner from Connect Key prefix.
 *
 * Vindi keys (new): fixed length 40 for prod and sandbox (same as PagBank CON… keys).
 * Legacy shorter CONVD… keys remain valid.
 */
class Detector
{
    private string $partner;

    private bool $sandbox;

    private ?string $templateCode;

    private string $connectKey;

    public function __construct(string $connectKey = '')
    {
        $this->connectKey = trim($connectKey);
        $this->sandbox = false;
        $this->templateCode = null;
        $this->partner = Partner::PAGBANK;
        $this->parse();
    }

    public static function fromKey(string $connectKey): self
    {
        return new self($connectKey);
    }

    private function parse(): void
    {
        $key = strtoupper($this->connectKey);
        if ($key === '') {
            return;
        }

        if (str_starts_with($key, 'CONVDSANDBOX') || str_starts_with($key, 'CONVD')) {
            $this->partner = Partner::VINDI;
            $this->sandbox = str_starts_with($key, 'CONVDSANDBOX');
            if (preg_match('/^CONVD(?:SANDBOX)?(30|14|FLX)/', $key, $matches)) {
                $map = ['30' => '30dias', '14' => '14dias', 'FLX' => 'flex'];
                $this->templateCode = $map[$matches[1]] ?? null;
            }
            return;
        }

        $this->partner = Partner::PAGBANK;
        $this->sandbox = str_starts_with($key, 'CONSANDBOX');
    }

    public function getPartner(): string
    {
        return $this->partner;
    }

    public function isVindi(): bool
    {
        return $this->partner === Partner::VINDI;
    }

    public function isPagBank(): bool
    {
        return $this->partner === Partner::PAGBANK;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function getTemplateCode(): ?string
    {
        return $this->templateCode;
    }

    public function getConnectKey(): string
    {
        return $this->connectKey;
    }

    /**
     * Last 4 chars for fingerprint / notices (never log the full key).
     */
    public function fingerprint(): string
    {
        if (strlen($this->connectKey) < 4) {
            return '';
        }

        return substr($this->connectKey, -4);
    }
}
