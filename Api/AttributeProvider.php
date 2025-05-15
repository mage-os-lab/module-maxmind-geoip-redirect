<?php

namespace MageOS\MaxMindGeoipRedirect\Api;

interface AttributeProvider
{
    const string CHECK_IP_API = 'api';
    const string CHECK_IP_LOCAL = 'local';
    const string IP_DB_DIRECTORY = 'maxmind' . DIRECTORY_SEPARATOR . 'geolite2';
    const string EXTRACT_PATH = self::IP_DB_DIRECTORY . DIRECTORY_SEPARATOR . 'extracted';
    const string GEOLITE2_COUNTRY_DB_TAR_GZ = 'GeoLite2-Country.tar.gz';
    const string GEOLITE2_COUNTRY_DB = 'GeoLite2-Country.mmdb';
    const string GEOLITE2_HOST = 'geolite.info';
    const string MAXMIND_COOKIE = 'maxmind_redirect';
    const string EVENT_DISPATCH_PREFIX = 'maxmind_';
}
