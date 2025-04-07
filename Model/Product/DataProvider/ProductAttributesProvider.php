<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class ProductAttributesProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'ProductAttributes';
    private string $encodedProductAttributes = '';

    private Attributes\Processor $attributeProcessor;

    public function __construct(
        Attributes\Processor $attributeProcessor
    ) {
        $this->attributeProcessor = $attributeProcessor;
    }

    public function getProductAttributesData(\M2E\OnBuy\Model\Product $product): array
    {
        $result = array_map(static function (\M2E\OnBuy\Model\Product\DataProvider\Attributes\Item $attribute) {
            return [
                'option_id' => $attribute->getValueId()
            ];
        }, $this->attributeProcessor->getAttributes($product));
        $this->collectWarningMessages($this->attributeProcessor->getWarningMessages());

        $this->encodedProductAttributes = json_encode($result);

        return $result;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => $this->encodedProductAttributes
        ];
    }

    private function collectWarningMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addWarningMessage($message);
        }
    }
}
