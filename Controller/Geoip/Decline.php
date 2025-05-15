<?php

namespace MageOS\MaxMindGeoipRedirect\Controller\Geoip;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use MageOS\MaxMindGeoipRedirect\Helper\ControllerHelper;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Exception\NoSuchEntityException;

class Decline implements HttpGetActionInterface
{
    /**
     * @param RedirectInterface $redirect
     * @param ModuleConfig $moduleConfig
     * @param ControllerHelper $controllerHelper
     * @param RedirectFactory $redirectFactory
     * @param StoreManagerInterface $storeManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     * @param ManagerInterface $eventManager
     * @param ResponseHttp $response
     */
    public function __construct(
        protected RedirectInterface $redirect,
        protected ModuleConfig $moduleConfig,
        protected ControllerHelper $controllerHelper,
        protected RedirectFactory $redirectFactory,
        protected StoreManagerInterface $storeManager,
        protected CookieMetadataFactory $cookieMetadataFactory,
        protected CookieManagerInterface $cookieManager,
        protected ManagerInterface $eventManager,
        protected ResponseHttp $response
    ) {
    }

    /**
     * @return ResultRedirect
     * @throws NoSuchEntityException
     */
    public function execute(): ResultRedirect
    {
        $this->response->setHeader('X-Magento-Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0', true);
        $this->response->setHeader('Pragma', 'no-cache', true);
        $this->response->setHeader('Expires', '0', true);

        $currentStoreCode = $this->storeManager->getStore()->getCode();

        $this->controllerHelper->setMaxMindCookie($currentStoreCode);

        $resultRedirect = $this->redirectFactory->create();

        $this->eventManager->dispatch(
            AttributeProvider::EVENT_DISPATCH_PREFIX . 'user_decline_geolocation',
            [
                'currentStoreCode' => $currentStoreCode
            ]
        );

        return $resultRedirect->setPath($this->redirect->getRefererUrl());
    }
}
