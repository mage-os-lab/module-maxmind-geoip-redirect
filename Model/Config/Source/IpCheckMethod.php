<?php

namespace MageOS\MaxMindGeoipRedirect\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageOS\MaxMindGeoipRedirect\Api\AttributeProvider;

class IpCheckMethod implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ],
            [
                'value' => AttributeProvider::CHECK_IP_API,
                'label' => __('API webservices (real-time updated)')
            ],
            [
                'value' => AttributeProvider::CHECK_IP_LOCAL,
                'label' => __('Downloaded local database (scheduled update)')
            ]
        ];
    }
}
