<?php

namespace MageOS\MaxMindGeoipRedirect\Controller\Geoip;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use MageOS\MaxMindGeoipRedirect\Helper\ControllerHelper;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use MageOS\MaxMindGeoipRedirect\Api\GeoloateIPInterface;
use MageOS\MaxMindGeoipRedirect\Api\CurrencyManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\EncoderInterface;
use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

class Redirect implements HttpGetActionInterface
{
    /**
     * @param RedirectInterface $redirect
     * @param ModuleConfig $moduleConfig
     * @param ControllerHelper $controllerHelper
     * @param RedirectFactory $redirectFactory
     * @param GeoloateIPInterface $geoloateIP
     * @param CurrencyManagerInterface $currencyManager
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $encoder
     * @param ManagerInterface $eventManager
     * @param ResponseHttp $response
     */
    public function __construct(
        protected RedirectInterface $redirect,
        protected ModuleConfig $moduleConfig,
        protected ControllerHelper $controllerHelper,
        protected RedirectFactory $redirectFactory,
        protected GeoloateIPInterface $geoloateIP,
        protected CurrencyManagerInterface $currencyManager,
        protected StoreManagerInterface $storeManager,
        protected UrlInterface $urlBuilder,
        protected EncoderInterface $encoder,
        protected ManagerInterface $eventManager,
        protected ResponseHttp $response
    ) {
    }

    /**
     * @return ResultRedirect
     * @throws NoSuchEntityException|LocalizedException
     */
    public function execute(): ResultRedirect
    {
        $this->response->setHeader('X-Magento-Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0', true);
        $this->response->setHeader('Pragma', 'no-cache', true);
        $this->response->setHeader('Expires', '0', true);

        $referrerUrl = $this->redirect->getRefererUrl();

        if (!$this->moduleConfig->isEnable()) {
            return $this->returnRedirect($referrerUrl);
        }

        $geolocationCountryCode = $this->geoloateIP->execute($this->controllerHelper->getClientIp());

        if (empty($geolocationCountryCode)) {
            return $this->returnRedirect($referrerUrl);
        }

        $targetStoreCode = $this->controllerHelper->getStoreViewByCountry($geolocationCountryCode);
        $targetUrl = $this->getTargetStoreRedirectUrl($referrerUrl, $targetStoreCode);

        $this->currencyManager->execute($targetStoreCode, $geolocationCountryCode);

        $this->eventManager->dispatch(
            AttributeProvider::EVENT_DISPATCH_PREFIX . 'user_geolocation_redirect',
            [
                'targetStoreCode' => $targetStoreCode,
                'geolocationCountryCode' => $geolocationCountryCode,
                'referrerUrl' => $referrerUrl
            ]
        );

        return $this->returnRedirect($targetUrl, $targetStoreCode);
    }

    /**
     * @param string $path
     * @param string $targetStoreCode
     * @return ResultRedirect
     * @throws NoSuchEntityException
     */
    protected function returnRedirect(string $path, string $targetStoreCode = ''): ResultRedirect
    {
        $targetStoreCode = empty($targetStoreCode) ? $this->storeManager->getStore()->getCode() : $targetStoreCode;

        $this->controllerHelper->setMaxMindCookie($targetStoreCode);

        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath($path);
    }

    /**
     * @param string $referrerUrl
     * @param string $targetStoreCode
     * @return string
     * @throws NoSuchEntityException|LocalizedException
     */
    protected function getTargetStoreRedirectUrl(string $referrerUrl, string $targetStoreCode = ''): string
    {
        $targetStoreCode = !empty($targetStoreCode) ? $targetStoreCode : $this->controllerHelper->getDefaultStoreView();
        $currentStoreCode = $this->storeManager->getStore()->getCode();

        if ($targetStoreCode === $currentStoreCode) {
            return $referrerUrl;
        }

        return $this->urlBuilder->getUrl(
            'stores/store/redirect',
            [
                '___store' => $targetStoreCode,
                '___from_store' => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($this->getRedirectUrl($referrerUrl, $targetStoreCode))
            ]
        );
    }

    /**
     * @param string $referrerUrl
     * @param string $targetStoreCode
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getRedirectUrl(string $referrerUrl, string $targetStoreCode = ''): string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $requestString = parse_url(str_replace($baseUrl, '', $referrerUrl))['path'];
        $isUseStoreInUrl = $this->storeManager->getStore($targetStoreCode)->isUseStoreInUrl();

        $redirectUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_DIRECT_LINK);
        $redirectUrl .= $isUseStoreInUrl ? $targetStoreCode . '/' : '';
        $redirectUrl .= $requestString;
        $redirectUrl .= !$isUseStoreInUrl ? '?___store=' . $targetStoreCode : '';

        return $redirectUrl;
    }
}
