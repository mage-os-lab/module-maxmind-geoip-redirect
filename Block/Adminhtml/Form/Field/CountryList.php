<?php

namespace MageOS\MaxMindGeoipRedirect\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Directory\Model\Config\Source\Country;

class CountryList extends Select
{
    /**
     * @param Context $context
     * @param Country $country
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected Country $country,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param $value
     * @return CountryList
     */
    public function setInputName($value): CountryList
    {
        return $this->setName($value . '[]');
    }

    /**
     * @param $value
     * @return CountryList
     */
    public function setInputId($value): CountryList
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setExtraParams('multiple="multiple"');
            $this->setOptions($this->getSourceOptions());
        }

        return parent::_toHtml();
    }

    /**
     * @return array
     */
    protected function getSourceOptions(): array
    {
        return $this->country->toOptionArray();
    }
}
