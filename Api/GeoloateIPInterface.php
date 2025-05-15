<?php

namespace MageOS\MaxMindGeoipRedirect\Api;

interface GeoloateIPInterface
{
    /**
     * @param string $ip
     * @return string
     */
    public function execute(string $ip): string;
}
