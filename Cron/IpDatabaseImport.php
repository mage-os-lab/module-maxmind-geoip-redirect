<?php


namespace MageOS\MaxMindGeoipRedirect\Cron;

use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use PharDataFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

class IpDatabaseImport
{
    /**
     * @param ModuleConfig $moduleConfig
     * @param Curl $curl
     * @param DirectoryList $directoryList
     * @param File $file
     * @param PharDataFactory $pharDataFactory
     */
    public function __construct(
        protected ModuleConfig $moduleConfig,
        protected Curl $curl,
        protected DirectoryList $directoryList,
        protected File $file,
        protected PharDataFactory $pharDataFactory
    ) {
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if (!$this->moduleConfig->isEnable() || $this->moduleConfig->getIpCheckMethod() === AttributeProvider::CHECK_IP_API) {
            return;
        }

        $this->downloadIPDatabase();
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function downloadIPDatabase(): void
    {
        $dbArchiveStream = $this->getDbArchive();

        $path = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . AttributeProvider::IP_DB_DIRECTORY;
        $filename = $path . DIRECTORY_SEPARATOR . AttributeProvider::GEOLITE2_COUNTRY_DB_TAR_GZ;

        $this->file->checkAndCreateFolder($path);
        $this->file->write($filename, $dbArchiveStream);

        $this->extractTarGzFile($filename, $path);
    }

    /**
     * @return string
     */
    protected function getDbArchive(): string
    {
        $accountId = $this->moduleConfig->getAccountId();
        $licenseKey = $this->moduleConfig->getLicenseKey();
        $databaseDownloadUrl = $this->moduleConfig->getDatabaseDownloadUrl();

        $this->curl->setCredentials($accountId, $licenseKey);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->curl->get($databaseDownloadUrl);

        return $this->curl->getBody();
    }

    /**
     * @param string $tarGz
     * @param string $maxMindPath
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function extractTarGzFile(string $tarGz, string $maxMindPath): void
    {
        $tar = str_replace('.gz', '', $tarGz);
        $extractPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . AttributeProvider::EXTRACT_PATH;
        $geoLiteDatabaseFile = '';

        $gzToTar = $this->pharDataFactory->create(['filename' => $tarGz]);
        $gzToTar->decompress();

        $tarToFolder = $this->pharDataFactory->create(['filename' => $tar]);
        $tarToFolder->extractTo($extractPath);

        $this->file->cd($extractPath);

        foreach ($this->file->ls(File::GREP_DIRS) as $folder) {
            $geoLiteDatabaseFile = $extractPath . DIRECTORY_SEPARATOR;
            $geoLiteDatabaseFile .= $folder['text'] . DIRECTORY_SEPARATOR;
            $geoLiteDatabaseFile .= AttributeProvider::GEOLITE2_COUNTRY_DB;
            break;
        }

        $this->file->cd($maxMindPath);
        $this->file->mv($geoLiteDatabaseFile, $maxMindPath . DIRECTORY_SEPARATOR . AttributeProvider::GEOLITE2_COUNTRY_DB);

        $this->file->rm($tarGz);
        $this->file->rm($tar);
        $this->file->rmdir($extractPath, true);
    }
}
