<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class DeliveryProvider implements DataBuilderInterface
{
    public const NICK = 'Delivery';

    public function getDeliveryTemplateId(\M2E\OnBuy\Model\Product $product): ?int
    {
        $listing = $product->getListing();

        if (!$listing->hasTemplateShipping()) {
            return null;
        }

        $shippingPolicy = $product->getShippingTemplate();

        return $shippingPolicy->getDeliveryTemplateId();
    }

    public function getWarningMessages(): array
    {
        return [];
    }
}
