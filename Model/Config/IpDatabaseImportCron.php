<?php

namespace MageOS\MaxMindGeoipRedirect\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Config\ValueFactory;
use Exception;

class IpDatabaseImportCron extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param string $modelPath
     * @param ValueFactory $valueFactory
     * @param string $expression
     * @param string $cronStringPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        protected ValueFactory $valueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        string $modelPath = '',
        protected string $expression = 'groups/general/fields/ip_database_import_cron/value',
        protected string $cronStringPath = 'crontab/ip_database_import/jobs/maxmind_ip_database_import/schedule/cron_expr',
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return IpDatabaseImportCron
     * @throws Exception
     */
    public function afterSave(): IpDatabaseImportCron
    {
        $expression = $this->getData($this->expression);

        try {
            $this->valueFactory->create()->load(
                $this->cronStringPath,
                'path'
            )->setValue(
                $expression
            )->setPath(
                $this->cronStringPath
            )->save();

        } catch (Exception $e) {
            throw new Exception(__('Some Thing Want Wrong , We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }
}
