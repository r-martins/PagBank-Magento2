<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\System\Config;

/**
 * Admin notes shared with Woo and Magento 1 when the Connect Key is Vindi.
 */
class VindiAdminNotes
{
    public const SALES_SETTINGS_URL = 'https://intermediador.yapay.com.br/settings/sales';

    public static function visualExpiryNote(string $methodLabel): string
    {
        $label = htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8');

        return '<em>Obs.: o valor acima é apenas visual. A real validade do '
            . $label
            . ' é configurada <a href="' . self::SALES_SETTINGS_URL . '" target="_blank" rel="noopener noreferrer">aqui</a>.</em>';
    }

    public static function boletoInstructionsNote(): string
    {
        return 'As linhas de instrução e logo podem ser configuradas '
            . '<a href="' . self::SALES_SETTINGS_URL . '" target="_blank" rel="noopener noreferrer">aqui</a> '
            . '(Configurações &gt; Transação de Vendas).';
    }
}
