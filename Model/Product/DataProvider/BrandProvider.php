<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class BrandProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Brand';

    private ?string $brandName = null;
    private \M2E\OnBuy\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\OnBuy\Model\Product\DataProvider\Attributes\NotFoundAttributeDetector $notFoundAttributeDetector;

    public function __construct(
        \M2E\OnBuy\Model\Category\Attribute\Repository                             $attributeRepository,
        \M2E\OnBuy\Model\Product\DataProvider\Attributes\NotFoundAttributeDetector $notFoundAttributeDetector
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->notFoundAttributeDetector = $notFoundAttributeDetector;
    }

    public function getProductBrand(\M2E\OnBuy\Model\Product $product): ?string
    {
        $brandAttribute = $this->attributeRepository->findByDictionaryId(
            $product->getTemplateCategoryId(),
            [\M2E\OnBuy\Model\Category\CategoryAttribute::ATTRIBUTE_TYPE_BRAND]
        );

        if (empty($brandAttribute)) {
            return null;
        }

        $brandAttribute = array_shift($brandAttribute);

        $magentoProduct = $product->getMagentoProduct();
        $this->notFoundAttributeDetector->clearMessages();
        $this->notFoundAttributeDetector->searchNotFoundAttributes($magentoProduct);

        $result = $this->processAttribute($brandAttribute, $magentoProduct);

        $this->notFoundAttributeDetector->processNotFoundAttributes(
            $magentoProduct,
            $product->getListing()->getStoreId(),
            (string)__('Brand')
        );

        $this->brandName = $result;

        return $this->brandName;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => $this->brandName
        ];
    }

    private function processAttribute(
        \M2E\OnBuy\Model\Category\CategoryAttribute $attribute,
        \M2E\OnBuy\Model\Magento\Product            $magentoProduct
    ): ?string {
        switch ($attribute->getValueMode()) {
            case \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_VALUE:
                $result = $attribute->getCustomValue();
                break;
            case \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE:
                $result = $magentoProduct->getAttributeValue($attribute->getCustomAttributeValue());
                break;
        }

        return !empty($result) ? $result : null;
    }
}
