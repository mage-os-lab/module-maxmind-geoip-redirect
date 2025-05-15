<?php

namespace MageOS\MaxMindGeoipRedirect\Service;

use MageOS\MaxMindGeoipRedirect\Api\CurrencyManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CurrencyManager implements CurrencyManagerInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected ModuleConfig $moduleConfig
    ) {
    }

    /**
     * @param string $storeCode
     * @param string $countryCode
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(string $storeCode, string $countryCode): void
    {
        $store = $this->storeManager->getStore($storeCode);
        $storeId = $store->getId();

        $currency = $this->moduleConfig->getCurrencyMapping($countryCode, $storeId);

        if (!empty($currency)) {
            try {
                $store->setCurrentCurrencyCode($currency);
            } catch (LocalizedException $e) {
                // do nothing
            }
        }
    }
}
