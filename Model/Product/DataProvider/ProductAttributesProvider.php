<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class ProductAttributesProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'ProductAttributes';

    private Attributes\Processor $attributeProcessor;

    public function __construct(
        Attributes\Processor $attributeProcessor
    ) {
        $this->attributeProcessor = $attributeProcessor;
    }

    public function getProductAttributesData(\M2E\OnBuy\Model\Product $product): Attributes\Value
    {
        $attributes = $this->attributeProcessor->getAttributes($product);

        $result = array_map(static function (\M2E\OnBuy\Model\Product\DataProvider\Attributes\Item $attribute) {
            return [
                'option_id' => $attribute->getValueId()
            ];
        }, $attributes);
        $this->collectWarningMessages($this->attributeProcessor->getWarningMessages());

        sort($result);
        $hash = \M2E\Core\Helper\Data::md5String(json_encode($result));

        return new \M2E\OnBuy\Model\Product\DataProvider\Attributes\Value($attributes, $hash);
    }

    private function collectWarningMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addWarningMessage($message);
        }
    }
}
