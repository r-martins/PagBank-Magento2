<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Stores that saved "external" (follow PagBank account) now use buyer interest.
 */
class MigrateExternalInstallmentOption implements DataPatchInterface
{
    private const CONFIG_PATH = 'payment/ricardomartins_pagbank_cc/installments_options';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => 'buyer'],
            [
                'path = ?' => self::CONFIG_PATH,
                'value = ?' => 'external',
            ]
        );

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
