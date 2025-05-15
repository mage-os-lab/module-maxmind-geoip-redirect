<?php

namespace MageOS\MaxMindGeoipRedirect\Controller\Geoip;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use MageOS\MaxMindGeoipRedirect\Helper\ControllerHelper;
use Magento\Framework\App\Request\Http as HttpRequest;
use MageOS\MaxMindGeoipRedirect\Api\GeoloateIPInterface;
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
     * @param GeoloateIPInterface $geoloateIP
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
        protected GeoloateIPInterface $geoloateIP,
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
            return $result->setData([false]);
        }

        $currentStore = $this->storeManager->getStore();
        $currentStoreCode = $currentStore->getCode();

        if ($this->cookieManager->getCookie(AttributeProvider::MAXMIND_COOKIE) === $currentStoreCode) {
            return $result->setData([false]);
        }

        $referrerUrl = $this->redirect->getRefererUrl();
        $userAgent = $this->httpRequest->getServer('HTTP_USER_AGENT');
        $storeId = $currentStore->getId();
        $currentIp = $this->controllerHelper->getClientIp();

        if (!$this->moduleConfig->showPopup($referrerUrl, $userAgent, $currentIp, $storeId)) {
            return $result->setData([false]);
        }

        $geolocationCountryCode = $this->geoloateIP->execute($currentIp);

        if (empty($geolocationCountryCode)) {
            return $result->setData([false]);
        }

        $targetStoreCode = $this->controllerHelper->getStoreViewByCountry($geolocationCountryCode);

        if ($targetStoreCode === $currentStoreCode) {
            return $result->setData([false]);
        }

        $country = $this->countryFactory->create()->loadByCode($geolocationCountryCode);

        $response = [
            true,
            $targetStoreCode ?: $this->controllerHelper->getDefaultStoreView(),
            $country->getName('en_US')
        ];

        $this->eventManager->dispatch(
            AttributeProvider::EVENT_DISPATCH_PREFIX . 'check_popup',
            [
                'targetStoreCode' => $targetStoreCode,
                'currentStoreCode' => $currentStoreCode,
                'geolocationCountryCode' => $geolocationCountryCode
            ]
        );

        return $result->setData($response);
    }
}
