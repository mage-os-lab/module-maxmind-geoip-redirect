<?php

namespace MageOS\MaxMindGeoipRedirect\Api;

final class AttributeProvider
{
    public const CHECK_IP_API = 'api';
    public const CHECK_IP_LOCAL = 'local';
    public const IP_DB_DIRECTORY = 'maxmind' . DIRECTORY_SEPARATOR . 'geolite2';
    public const EXTRACT_PATH = self::IP_DB_DIRECTORY . DIRECTORY_SEPARATOR . 'extracted';
    public const GEOLITE2_COUNTRY_DB_TAR_GZ = 'GeoLite2-Country.tar.gz';
    public const GEOLITE2_COUNTRY_DB = 'GeoLite2-Country.mmdb';
    public const GEOLITE2_HOST = 'geolite.info';
    public const MAXMIND_COOKIE = 'maxmind_redirect';
    public const EVENT_DISPATCH_PREFIX = 'maxmind_';

    private function __construct() {}
}
