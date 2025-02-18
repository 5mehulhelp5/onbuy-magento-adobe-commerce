<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing;

abstract class TypeSwitcher extends \M2E\OnBuy\Block\Adminhtml\Switcher
{
    public const LISTING_TYPE_M2E = 'product';
    public const LISTING_TYPE_LISTING_OTHER = 'other';

    protected $paramName = 'listing_type';

    public function getLabel()
    {
        return (string)__('Listing Type');
    }

    public function hasDefaultOption(): bool
    {
        return false;
    }

    protected function loadItems()
    {
        $this->items = [
            'mode' => [
                'value' => [
                    [
                        'label' => \M2E\OnBuy\Helper\Module::getExtensionTitle(),
                        'value' => self::LISTING_TYPE_M2E,
                    ],
                    [
                        'label' => __('Unmanaged'),
                        'value' => self::LISTING_TYPE_LISTING_OTHER,
                    ],
                ],
            ],
        ];
    }
}
