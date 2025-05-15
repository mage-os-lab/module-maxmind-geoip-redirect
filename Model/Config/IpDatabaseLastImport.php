<?php

namespace MageOS\MaxMindGeoipRedirect\Model\Config;

use Magento\Framework\App\Config\Value;

use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Exception\FileSystemException;

class IpDatabaseLastImport extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param DirectoryList $directoryList
     * @param File $file
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        protected DirectoryList $directoryList,
        protected File $file,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterLoad(): IpDatabaseLastImport
    {
        try {
            $databaseIpPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR;
            $databaseIpPath .= AttributeProvider::IP_DB_DIRECTORY . DIRECTORY_SEPARATOR;
            $databaseIpPath .= AttributeProvider::GEOLITE2_COUNTRY_DB;

            if (!$this->file->fileExists($databaseIpPath)) {
                $this->setValue(__('No database downloaded yet'));
            } else {
                $lastDownload = date('Y-m-d H:i:s', filemtime($databaseIpPath));
                $this->setValue(__('Latest database download: ') . $lastDownload);
            }
        } catch (FileSystemException $e) {
            $this->setValue(__('No database downloaded yet'));
        }

        return $this;
    }
}
