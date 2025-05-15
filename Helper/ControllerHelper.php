<?php

namespace MageOS\MaxMindGeoipRedirect\Helper;

use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig as ModuleConfig;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;

class ControllerHelper
{
    /**
     * @param HttpRequest $httpRequest
     * @param StoreManagerInterface $storeManager
     * @param ModuleConfig $moduleConfig
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        protected HttpRequest $httpRequest,
        protected StoreManagerInterface $storeManager,
        protected ModuleConfig $moduleConfig,
        protected CookieMetadataFactory $cookieMetadataFactory,
        protected CookieManagerInterface $cookieManager
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
        foreach ($this->storeManager->getStores() as $store) {
            $redirectCountryList = $this->moduleConfig->getAffectedCountries($store->getId());

            if (in_array($countryCode, $redirectCountryList)) {
                return $store->getCode();
            }
        }

        return false;
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
}
