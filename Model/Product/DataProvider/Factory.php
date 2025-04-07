<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class Factory
{
    private const ALLOWED_BUILDERS = [
        PriceProvider::NICK => PriceProvider::class,
        QtyProvider::NICK => QtyProvider::class,
        DeliveryProvider::NICK => DeliveryProvider::class,
        IdentifierProvider::NICK => IdentifierProvider::class,
        TitleProvider::NICK => TitleProvider::class,
        DescriptionProvider::NICK => DescriptionProvider::class,
        ImagesProvider::NICK => ImagesProvider::class,
        CategoryProvider::NICK => CategoryProvider::class,
        ProductAttributesProvider::NICK => ProductAttributesProvider::class,
        BrandProvider::NICK => BrandProvider::class
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $nick): DataBuilderInterface
    {
        if (!isset(self::ALLOWED_BUILDERS[$nick])) {
            throw new \M2E\OnBuy\Model\Exception\Logic(sprintf('Unknown builder - %s', $nick));
        }

        /** @var DataBuilderInterface */
        return $this->objectManager->create(self::ALLOWED_BUILDERS[$nick]);
    }
}
