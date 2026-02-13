<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use RicardoMartins\PagBank\Api\Connect\PublicKeyInterface;

class ConnectKey extends Value
{
    public function __construct(
        private readonly PublicKeyInterface $publicKey,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return AbstractModel
     * @throws LocalizedException
     */
    public function beforeSave(): AbstractModel
    {
        $connectKey = $this->getValue();
        $oldConnectKey = $this->getOldValue();

        if ($connectKey === $oldConnectKey) {
            return parent::beforeSave();
        }

        $scope = $this->getScope();
        $scopeId = (int) $this->getScopeId();

        try {
            $publicKey = $this->publicKey->createPublicKey($connectKey);
            $this->publicKey->savePublicKey($publicKey, $scope, $scopeId);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Public Key Error: %1', $e->getMessage()));
        }

        return parent::beforeSave();
    }
}
