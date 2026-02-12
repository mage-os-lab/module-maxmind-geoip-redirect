<?php

namespace MageOS\MaxMindGeoipRedirect\Controller\Geoip;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use MageOS\MaxMindGeoipRedirect\Helper\ControllerHelper;
use Magento\Framework\App\Request\Http as HttpRequest;
use MageOS\MaxMindGeoipRedirect\Api\GeolocateIPInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

class CheckPopup implements HttpGetActionInterface
{
    /**
     * @param RedirectInterface $redirect
     * @param ModuleConfig $moduleConfig
     * @param ControllerHelper $controllerHelper
     * @param HttpRequest $httpRequest
     * @param GeolocateIPInterface $geolocateIP
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $jsonFactory
     * @param CookieManagerInterface $cookieManager
     * @param ManagerInterface $eventManager
     * @param ResponseHttp $response
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        protected RedirectInterface $redirect,
        protected ModuleConfig $moduleConfig,
        protected ControllerHelper $controllerHelper,
        protected HttpRequest $httpRequest,
        protected GeolocateIPInterface $geolocateIP,
        protected StoreManagerInterface $storeManager,
        protected JsonFactory $jsonFactory,
        protected CookieManagerInterface $cookieManager,
        protected ManagerInterface $eventManager,
        protected ResponseHttp $response,
        protected CountryFactory $countryFactory
    ) {
    }

    /**
     * @return Json|ResultInterface|ResponseInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(): Json|ResultInterface|ResponseInterface
    {
        $this->response->setHeader('X-Magento-Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0', true);
        $this->response->setHeader('Pragma', 'no-cache', true);
        $this->response->setHeader('Expires', '0', true);

        $result = $this->jsonFactory->create();

        if (!$this->moduleConfig->isEnable()) {
            return $result->setData(['showPopup' => false]);
        }

        $currentStore = $this->storeManager->getStore();
        $currentStoreCode = $currentStore->getCode();

        if ($this->cookieManager->getCookie(AttributeProvider::MAXMIND_COOKIE) === $currentStoreCode) {
            return $result->setData(['showPopup' => false]);
        }

        $referrerUrl = $this->redirect->getRefererUrl();
        $userAgent = $this->httpRequest->getServer('HTTP_USER_AGENT');
        $storeId = $currentStore->getId();
        $currentIp = $this->controllerHelper->getClientIp();

        if (!$this->moduleConfig->showPopup($referrerUrl, $userAgent, $currentIp, $storeId)) {
            return $result->setData(['showPopup' => false]);
        }

        $geolocationCountryCode = $this->geolocateIP->execute($currentIp);

        if (empty($geolocationCountryCode)) {
            return $result->setData(['showPopup' => false]);
        }

        $targetStoreCode = $this->controllerHelper->getStoreViewByCountry($geolocationCountryCode);

        if ($targetStoreCode === $currentStoreCode) {
            return $result->setData(['showPopup' => false]);
        }

        $country = $this->countryFactory->create()->loadByCode($geolocationCountryCode);

        $response = [
            'showPopup' => true,
            'targetStore' => $targetStoreCode ?: $this->controllerHelper->getDefaultStoreView(),
            'countryName' => $country->getName('en_US')
        ];

        $this->eventManager->dispatch(
            AttributeProvider::EVENT_DISPATCH_PREFIX . 'check_popup',
            [
                'targetStoreCode' => $targetStoreCode,
                'currentStoreCode' => $currentStoreCode,
                'geolocationCountryCode' => $geolocationCountryCode
            ]
        );

        $currentStoreId = $currentStore->getId();
        $targetStoreId = $this->storeManager->getStore($targetStoreCode)->getId();

        return $result->setData($this->checkPopupTranslation($response, $currentStoreId, $targetStoreId));
    }

    /**
     * @param array $response
     * @param string $currentStoreId
     * @param string $targetStoreId
     * @return array
     */
    protected function checkPopupTranslation(array $response, string $currentStoreId, string $targetStoreId): array
    {
        if (empty($targetStoreId) || empty($currentStoreId)) {
            return $response;
        }

        switch ($this->moduleConfig->getPopupLanguageMode()) {
            case 'target_store':
                $popupText = $this->moduleConfig->getRedirectPopupText($targetStoreId);
                $acceptButton = $this->moduleConfig->getPopupAcceptButtonText($targetStoreId);
                $declineButton = $this->moduleConfig->getPopupDeclineButtonText($targetStoreId);
                $translationStoreId = $targetStoreId;
                break;
            case 'current_store':
                $popupText = $this->moduleConfig->getRedirectPopupText($currentStoreId);
                $acceptButton = $this->moduleConfig->getPopupAcceptButtonText($currentStoreId);
                $declineButton = $this->moduleConfig->getPopupDeclineButtonText($currentStoreId);
                $translationStoreId = $currentStoreId;
                break;
            case 'global':
            default:
                return $response;
        }

        $response['countryName'] = $this->controllerHelper->translateCountryName($response['countryName'], (int)$translationStoreId);
        $response['popupText'] = $popupText;
        $response['acceptLabel'] = $acceptButton;
        $response['declineLabel'] = $declineButton;

        return $response;
    }
}
