<?php

namespace MageOS\MaxMindGeoipRedirect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ModuleConfig extends AbstractHelper
{
    const string SECTION = 'maxmind_geoip_redirect/';

    const string GENERAL_GROUP = self::SECTION . 'general/';
    const string MAXMIND_SETTINGS_GROUP = self::SECTION . 'maxmind_settings/';
    const string COUNTRY_REDIRECT_SETTINGS = self::SECTION . 'country_redirect_settings/';
    const string RESTRICTION = self::SECTION . 'restriction/';

    const string ENABLE = self::GENERAL_GROUP . 'enable';
    const string FORCED_IP = self::GENERAL_GROUP . 'forced_ip';
    const string IP_CHECK_METHOD = self::GENERAL_GROUP . 'ip_check_method';
    const string REDIRECT_POPUP_TEXT = self::GENERAL_GROUP . 'redirect_popup_text';
    const string POPUP_LANGUAGE_MODE = self::GENERAL_GROUP . 'popup_language_mode';
    const string POPUP_ACCEPT_BUTTON_TEXT = self::GENERAL_GROUP . 'redirect_popup_accept_button_text';
    const string POPUP_DECLINE_BUTTON_TEXT = self::GENERAL_GROUP . 'redirect_popup_decline_button_text';

    const string ACCOUNT_ID = self::MAXMIND_SETTINGS_GROUP . 'account_id';
    const string LICENSE_KEY = self::MAXMIND_SETTINGS_GROUP . 'license_key';
    const string DATABASE_DOWNLOAD_URL = self::MAXMIND_SETTINGS_GROUP . 'database_download_url';

    const string AFFECTED_COUNTRIES = self::COUNTRY_REDIRECT_SETTINGS . 'affected_countries';
    const string CURRENCY_MAPPING = self::COUNTRY_REDIRECT_SETTINGS . 'currency_mapping';

    const string EXCEPTED_IPS = self::RESTRICTION . 'ips';
    const string EXCEPTED_URLS = self::RESTRICTION . 'urls';
    const string EXCEPTED_USER_AGENTS = self::RESTRICTION . 'user_agents';
    const string FIRST_VISIT_ONLY = self::RESTRICTION . 'first_visit_only';

    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        protected SerializerInterface $serializer
    ) {
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->scopeConfig->isSetFlag(self::ENABLE);
    }

    public function forcedIp(): string
    {
        return (string)$this->scopeConfig->getValue(self::FORCED_IP);
    }

    /**
     * @return string
     */
    public function getIpCheckMethod(): string
    {
        return (string)$this->scopeConfig->getValue(self::IP_CHECK_METHOD);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getRedirectPopupText(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::REDIRECT_POPUP_TEXT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getPopupAcceptButtonText(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(self::POPUP_ACCEPT_BUTTON_TEXT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getPopupDeclineButtonText(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(self::POPUP_DECLINE_BUTTON_TEXT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getPopupLanguageMode(): string
    {
        return (string)$this->scopeConfig->getValue(self::POPUP_LANGUAGE_MODE);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getStoreLocale(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getAccountId(): string
    {
        return (string)$this->scopeConfig->getValue(self::ACCOUNT_ID);
    }

    /**
     * @return string
     */
    public function getLicenseKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::LICENSE_KEY);
    }

    /**
     * @return string
     */
    public function getDatabaseDownloadUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::DATABASE_DOWNLOAD_URL);
    }

    /**
     * @param string $url
     * @param string $userAgent
     * @param string $ip
     * @param int $storeId
     * @return bool
     */
    public function showPopup(string $url, string $userAgent, string $ip, int $storeId = 0): bool
    {
        if (!$this->isEnable()) {
            return false;
        }

        if (!$this->isExceptedUrls($url, $storeId) && !$this->isExceptedUserAgents($userAgent, $storeId) && !$this->isExceptedIp($ip, $storeId)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAffectedCountries(int $storeId): array
    {
        return explode(',', $this->scopeConfig->getValue(self::AFFECTED_COUNTRIES, ScopeInterface::SCOPE_STORE, $storeId));
    }

    public function getCurrencyMapping(string $countryCode, int $storeId = 0): string
    {
        $currencyMapping = (string)$this->scopeConfig->getValue(self::CURRENCY_MAPPING, ScopeInterface::SCOPE_STORE, $storeId);
        $currencyMapping = $this->serializer->unserialize($currencyMapping);

        foreach ($currencyMapping as $item) {
            if (in_array($countryCode, $item['country_list'])) {
                return $item['currency_list'];
            }
        }

        return '';
    }

    /**
     * @param string $ip
     * @param int $storeId
     * @return bool
     */
    public function isExceptedIp(string $ip, int $storeId = 0): bool
    {
        $exceptedIps = (string)$this->scopeConfig->getValue(self::EXCEPTED_IPS, ScopeInterface::SCOPE_STORE, $storeId);
        $exceptedIps = preg_split("/\r\n|\n|\r/", $exceptedIps);

        return in_array($ip, $exceptedIps);
    }

    /**
     * @param string $url
     * @param int $storeId
     * @return bool
     */
    public function isExceptedUrls(string $url, int $storeId = 0): bool
    {
        $exceptedUrls = (string)$this->scopeConfig->getValue(self::EXCEPTED_URLS, ScopeInterface::SCOPE_STORE, $storeId);
        return $this->checkExceptedParams($url, $exceptedUrls);
    }

    /**
     * @param string $userAgent
     * @param int $storeId
     * @return bool
     */
    public function isExceptedUserAgents(string $userAgent, int $storeId = 0): bool
    {
        $exceptedUserAgents = (string)$this->scopeConfig->getValue(self::EXCEPTED_USER_AGENTS, ScopeInterface::SCOPE_STORE, $storeId);
        return $this->checkExceptedParams($userAgent, $exceptedUserAgents);
    }

    /**
     * @return bool
     */
    public function firstVisitOnly(): bool
    {
        return $this->scopeConfig->isSetFlag(self::FIRST_VISIT_ONLY);
    }

    /**
     * @param $pattern
     * @return bool
     */
    protected function isRegex($pattern): bool
    {
        return @preg_match($pattern, '') !== false;
    }

    /**
     * @param string $paramToCheck
     * @param string $exceptedParams
     * @return bool
     */
    protected function checkExceptedParams(string $paramToCheck, string $exceptedParams): bool
    {
        if (empty($exceptedParams)) {
            return false;
        }

        $exceptedParams = preg_split("/\r\n|\n|\r/", $exceptedParams);

        foreach ($exceptedParams as $exceptedParam) {
            if (($this->isRegex($exceptedParam) && preg_match($exceptedParam, $paramToCheck) === 1) || str_contains($paramToCheck, $exceptedParam)) {
                return true;
            }
        }

        return false;
    }
}
