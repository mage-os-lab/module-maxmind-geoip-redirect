<?php

namespace MageOS\MaxMindGeoipRedirect\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageOS\MaxMindGeoipRedirect\Helper\ModuleConfig;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class Popup implements ArgumentInterface
{
    /**
     * @param ModuleConfig $moduleConfig
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected ModuleConfig $moduleConfig,
        protected FilterProvider $filterProvider,
        protected StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return string
     */
    public function getPopupMessage(): string
    {
        $popupText = $this->moduleConfig->getRedirectPopupText();

        try {
            return (!empty($popupText)) ? $this->filterProvider->getPageFilter()->filter($popupText) : $popupText;
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        try {
            return $this->storeManager->getStore()->getCode();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }
}
