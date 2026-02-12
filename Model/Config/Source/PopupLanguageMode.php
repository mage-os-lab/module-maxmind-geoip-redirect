<?php

namespace MageOS\MaxMindGeoipRedirect\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PopupLanguageMode implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => 'target_store', 'label' => __('Target Store')],
            ['value' => 'current_store', 'label' => __('Current Store')],
            ['value' => 'global', 'label' => __('Global')]
        ];
    }
}
