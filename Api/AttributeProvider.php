<?php

namespace MageOS\MaxMindGeoipRedirect\Api;

interface AttributeProvider
{
    const CHECK_IP_API = 'api';
    const CHECK_IP_LOCAL = 'local';
    const IP_DB_DIRECTORY = 'maxmind' . DIRECTORY_SEPARATOR . 'geolite2';
    const EXTRACT_PATH = self::IP_DB_DIRECTORY . DIRECTORY_SEPARATOR . 'extracted';
    const GEOLITE2_COUNTRY_DB_TAR_GZ = 'GeoLite2-Country.tar.gz';
    const GEOLITE2_COUNTRY_DB = 'GeoLite2-Country.mmdb';
    const GEOLITE2_HOST = 'geolite.info';
    const MAXMIND_COOKIE = 'maxmind_redirect';
    const EVENT_DISPATCH_PREFIX = 'maxmind_';
}
