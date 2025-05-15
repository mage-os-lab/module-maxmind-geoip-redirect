<?php

namespace MageOS\MaxMindGeoipRedirect\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class CurrencyMap extends AbstractFieldArray
{
    protected ?BlockInterface $countryList = null;
    protected ?BlockInterface $currencyList = null;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('country_list', [
            'label' => __('Countries'),
            'renderer' => $this->getCountryListRender()
        ]);

        $this->addColumn('currency_list', [
            'label' => __('Currency'),
            'renderer' => $this->getCurrencyListRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $status = $row->getCurrencyList();
        if ($status !== null) {
            $options['option_' . $this->getCurrencyListRenderer()->calcOptionHash($status)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return CountryList
     * @throws LocalizedException
     */
    protected function getCountryListRender(): CountryList
    {
        if (!$this->countryList) {
            $this->countryList = $this->getLayout()->createBlock(
                CountryList::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->countryList;
    }

    /**
     * @return CurrencyList
     * @throws LocalizedException
     */
    protected function getCurrencyListRenderer(): CurrencyList
    {
        if (!$this->currencyList) {
            $this->currencyList = $this->getLayout()->createBlock(
                CurrencyList::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->currencyList;
    }
}
