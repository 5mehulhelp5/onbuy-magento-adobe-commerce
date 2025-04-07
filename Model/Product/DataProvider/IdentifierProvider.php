<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class IdentifierProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Identifier';

    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Settings $settings
    ) {
        $this->settings = $settings;
    }

    public function getIdentifier(\M2E\OnBuy\Model\Product $product): string
    {
        $eanAttributeCode = $this->settings->getIdentifierCodeValue();
        $magentoProduct = $product->getMagentoProduct();

        return $magentoProduct->getAttributeValue($eanAttributeCode);
    }
}
