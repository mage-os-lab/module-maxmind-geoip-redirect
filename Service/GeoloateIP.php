<?php

namespace MageOS\MaxMindGeoipRedirect\Service;

use MageOS\MaxMindGeoipRedirect\Api\GeoloateIPInterface;
use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use GeoIp2\WebService\ClientFactory;
use GeoIp2\Database\ReaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Exception\AuthenticationException;
use GeoIp2\Exception\GeoIp2Exception;
use GeoIp2\Exception\HttpException;
use GeoIp2\Exception\InvalidRequestException;
use GeoIp2\Exception\OutOfQueriesException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Magento\Framework\Exception\FileSystemException;
use InvalidArgumentException;

class GeoloateIP implements GeoloateIPInterface, AttributeProvider
{
    /**
     * @param ModuleConfig $moduleConfig
     * @param ClientFactory $clientFactory
     * @param ReaderFactory $readerFactory
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        protected ModuleConfig $moduleConfig,
        protected ClientFactory $clientFactory,
        protected ReaderFactory $readerFactory,
        protected DirectoryList $directoryList,
        protected File $file
    ) {
    }

    /**
     * @param string $ip
     * @return string
     */
    public function execute(string $ip): string
    {
        if (!$this->moduleConfig->isEnable()) {
            return '';
        }

        $ip = empty($this->moduleConfig->forcedIp()) ? $ip : $this->moduleConfig->forcedIp();

        if ($this->moduleConfig->getIpCheckMethod() === self::CHECK_IP_API) {
            return $this->useApi($ip);
        } elseif ($this->moduleConfig->getIpCheckMethod() === self::CHECK_IP_LOCAL) {
            return $this->useLocal($ip);
        }

        return '';
    }

    /**
     * @param string $ip
     * @return string
     */
    protected function useApi(string $ip): string
    {
        $accountId = $this->moduleConfig->getAccountId();
        $licenseKey = $this->moduleConfig->getLicenseKey();
        $options = ['host' => self::GEOLITE2_HOST];

        $client = $this->clientFactory->create(['accountId' => $accountId, 'licenseKey' => $licenseKey, 'options' => $options]);

        try {
            return (string)$client->country($ip)->country->isoCode;
        } catch (AddressNotFoundException|AuthenticationException|InvalidRequestException|HttpException|OutOfQueriesException|GeoIp2Exception $e) {
            return '';
        }
    }

    /**
     * @param string $ip
     * @return string
     */
    protected function useLocal(string $ip): string
    {
        try {
            $databaseIpPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR;
        } catch (FileSystemException $e) {
            return $this->useApi($ip);
        }

        $databaseIpPath .= self::IP_DB_DIRECTORY . DIRECTORY_SEPARATOR;
        $databaseIpPath .= self::GEOLITE2_COUNTRY_DB;

        if (!$this->file->fileExists($databaseIpPath)) {
            return $this->useApi($ip);
        }

        $reader = $this->readerFactory->create(['filename' => $databaseIpPath]);

        try {
            return (string)$reader->country($ip)->country->isoCode;
        } catch (AddressNotFoundException|InvalidDatabaseException|InvalidArgumentException $e) {
            return '';
        }
    }
}
