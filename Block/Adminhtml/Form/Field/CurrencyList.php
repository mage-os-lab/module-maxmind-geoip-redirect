<?php

namespace MageOS\MaxMindGeoipRedirect\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\CurrencySymbol\Model\System\Currencysymbol;

class CurrencyList extends Select
{
    /**
     * @param Context $context
     * @param Currencysymbol $currency
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected Currencysymbol $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param $value
     * @return CurrencyList
     */
    public function setInputName($value): CurrencyList
    {
        return $this->setName($value);
    }

    /**
     * @param $value
     * @return CurrencyList
     */
    public function setInputId($value): CurrencyList
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        foreach ($this->getSourceOptions() as $code => $currency) {
            if (isset($currency['displayName']) && $currency['displayName']) {
                $this->addOption($code, $currency['displayName']);
            }
        }
        array_unshift($this->_options, ['value' => '', 'label' => __('--Please Select--')]);

        return parent::_toHtml();
    }

    /**
     * @return array
     */
    protected function getSourceOptions(): array
    {
        return $this->currency->getCurrencySymbolsData();
    }
}
