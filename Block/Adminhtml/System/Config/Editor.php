<?php

namespace MageOS\MaxMindGeoipRedirect\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class Editor extends Field
{
    /**
     * @param Context $context
     * @param WysiwygConfig $wysiwygConfig
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected WysiwygConfig $wysiwygConfig,
        protected RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $params = $this->request->getParams();
        if (isset($params[ScopeInterface::SCOPE_STORE])) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeId = $params[ScopeInterface::SCOPE_STORE];
        } elseif (isset($params[ScopeInterface::SCOPE_WEBSITE])) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $params[ScopeInterface::SCOPE_WEBSITE];
        } else {
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeId = null;
        }

        $configRedirectionDecline = $this->_scopeConfig->getValue(
            'maxmind_geoip_redirect/general/redirect_popup_text',
            $scope,
            $scopeId
        );
        $element->setWysiwyg(true);
        $wysiwygConfig = $this->wysiwygConfig->getConfig($element);
        if (!$configRedirectionDecline) {
            $wysiwygConfig->setData('hidden', true);
            $element->setConfig($wysiwygConfig);

            return parent::_getElementHtml($element);
        }

        $element->setConfig($wysiwygConfig);

        return parent::_getElementHtml($element);
    }
}
