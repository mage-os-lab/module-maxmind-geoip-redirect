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
            '' => __('-- Please Select --'),
            'target_store' => __('Target Store'),
            'current_store' => __('Current Store'),
            'global' => __('Global')
        ];
    }
}
