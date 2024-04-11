<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class PixExpiration extends Value
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ManagerInterface $messageManager,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return AbstractModel
     */
    public function beforeSave(): AbstractModel
    {
        $pixExpitationTime = $this->getValue();
        $pixExpitationTimeOldValue = $this->getOldValue();

        if ($pixExpitationTime === $pixExpitationTimeOldValue) {
            return parent::beforeSave();
        }

        $scope = $this->getScope();
        $scopeId = (int) $this->getScopeId();

        $pendingPaymentLifetime = $this->scopeConfig->getValue('sales/orders/delete_pending_after', $scope, $scopeId);

        if ($pixExpitationTime > $pendingPaymentLifetime) {
            $this->setValue($pendingPaymentLifetime);
            $this->messageManager->addWarningMessage(__('Pix expiration time must be less than or equal to the pending payment lifetime. The saved value is %1 minutes and can be changed in: Sales -> Sales -> Orders Cron Settings -> Pending Payment Order Lifetime.', $pendingPaymentLifetime));
        }

        return parent::beforeSave();
    }
}
