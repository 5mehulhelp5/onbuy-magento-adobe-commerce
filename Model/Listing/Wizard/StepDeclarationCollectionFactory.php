<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing\Wizard;

use M2E\OnBuy\Model\Listing\Wizard;

class StepDeclarationCollectionFactory
{
    public const STEP_SELECT_PRODUCT_SOURCE = 'products-source';
    public const STEP_SELECT_PRODUCTS = 'select-products';
    public const STEP_SETTINGS_IDENTIFIER = 'settings-identifier';
    public const STEP_SEARCH_PRODUCTS_CHANNEL_ID = 'search-products-channel-id';
    public const STEP_SELECT_CATEGORY_MODE = 'category-mode';
    public const STEP_SELECT_CATEGORY = 'select-category';
    public const STEP_REVIEW = 'review';

    private static array $steps = [
        Wizard::TYPE_GENERAL => [
            [
                'nick' => self::STEP_SELECT_PRODUCT_SOURCE,
                'route' => '*/listing_wizard_productSource/view',
                'back_handler' => null,
            ],
            [
                'nick' => self::STEP_SELECT_PRODUCTS,
                'route' => '*/listing_wizard_product/view',
                'back_handler' => \M2E\OnBuy\Model\Listing\Wizard\Step\BackHandler\Products::class,
            ],
            [
                'nick' => self::STEP_SETTINGS_IDENTIFIER,
                'route' => '*/listing_wizard_settings/view',
                'back_handler' => \M2E\OnBuy\Model\Listing\Wizard\Step\BackHandler\Products::class
            ],
            [
                'nick' => self::STEP_SEARCH_PRODUCTS_CHANNEL_ID,
                'route' => '*/listing_wizard_search/view',
                'back_handler' => \M2E\OnBuy\Model\Listing\Wizard\Step\BackHandler\SearchChannelId::class,
            ],
            [
                'nick' => self::STEP_REVIEW,
                'route' => '*/listing_wizard_review/view',
                'back_handler' => null,
            ],
        ],
        Wizard::TYPE_UNMANAGED => [
            [
                'nick' => self::STEP_REVIEW,
                'route' => '*/listing_wizard_review/view/type/unmanaged',
                'back_handler' => null,
            ],
        ],
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $type): StepDeclarationCollection
    {
        $this->validateType($type);

        $steps = [];
        foreach (self::$steps[$type] as $stepData) {
            $steps[] = new StepDeclaration($stepData['nick'], $stepData['route'], $stepData['back_handler']);
        }

        return $this->objectManager->create(StepDeclarationCollection::class, ['steps' => $steps]);
    }

    private function validateType(string $type): void
    {
        if (!in_array($type, [Wizard::TYPE_GENERAL, Wizard::TYPE_UNMANAGED])) {
            throw new \LogicException(sprintf('Listing Wizard type "%s" is not valid.', $type));
        }
    }
}
