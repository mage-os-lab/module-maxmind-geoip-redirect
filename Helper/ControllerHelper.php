<?php

namespace MageOS\MaxMindGeoipRedirect\Helper;

use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig as ModuleConfig;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;

class ControllerHelper
{
    /**
     * @var array|null
     */
    private ?array $countryStoreMap = null;

    /**
     * @param HttpRequest $httpRequest
     * @param StoreManagerInterface $storeManager
     * @param ModuleConfig $moduleConfig
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     * @param CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        protected HttpRequest $httpRequest,
        protected StoreManagerInterface $storeManager,
        protected ModuleConfig $moduleConfig,
        protected CookieMetadataFactory $cookieMetadataFactory,
        protected CookieManagerInterface $cookieManager,
        protected CollectionFactory $countryCollectionFactory
    ) {
    }

    /**
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        $ip = explode(',', $this->httpRequest->getClientIp());
        return array_shift($ip);
    }

    /**
     * @param $countryCode
     * @return false|string
     */
    public function getStoreViewByCountry($countryCode): false|string
    {
        if ($this->countryStoreMap === null) {
            $this->countryStoreMap = [];
            foreach ($this->storeManager->getStores() as $store) {
                $redirectCountryList = $this->moduleConfig->getAffectedCountries($store->getId());
                foreach ($redirectCountryList as $country) {
                    $this->countryStoreMap[$country] = $store->getCode();
                }
            }
        }

        return $this->countryStoreMap[$countryCode] ?? false;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getDefaultStoreView(): string
    {
        $mainWebsite = $this->storeManager->getWebsite(true);
        $defaultStore = $mainWebsite->getDefaultStore();

        return $defaultStore->getCode();
    }

    /**
     * @param $storeCode
     * @return void
     */
    public function setMaxMindCookie($storeCode): void
    {
        try {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath('/');
            if ($this->moduleConfig->firstVisitOnly()) {
                $metadata->setDuration(3600 * 24 * 365 * 10);
            }

            $this->cookieManager->setPublicCookie(AttributeProvider::MAXMIND_COOKIE, $storeCode, $metadata);
        } catch (InputException|CookieSizeLimitReachedException|FailureToSendException $e) {
            /** Do nothing */
        }
    }

    /**
     * @param string $name
     * @param int $storeId
     * @return string
     */
    public function translateCountryName(string $name, int $storeId): string
    {
        $toLocale = $this->moduleConfig->getStoreLocale($storeId);
        $countryCollection = $this->countryCollectionFactory->create();
        $countryCollection->addFieldToFilter('country_id', ['neq' => '']);

        $nameIndex = [];
        foreach ($countryCollection as $country) {
            $nameIndex[strtolower((string)$country->getName('en_US'))] = $country;
        }

        $key = strtolower($name);
        if (isset($nameIndex[$key])) {
            return $nameIndex[$key]->getName($toLocale);
        }

        return $name;
    }
}
