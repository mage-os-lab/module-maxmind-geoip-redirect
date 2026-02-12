<?php

namespace MageOS\MaxMindGeoipRedirect\Api;

interface GeolocateIPInterface
{
    /**
     * @param string $ip
     * @return string
     */
    public function execute(string $ip): string;
}
