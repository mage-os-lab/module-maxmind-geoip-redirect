<?php

namespace MageOS\MaxMindGeoipRedirect\Api;

use Magento\Framework\Exception\NoSuchEntityException;

interface CurrencyManagerInterface
{
    /**
     * @param string $storeCode
     * @param string $countryCode
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(string $storeCode, string $countryCode): void;
}
