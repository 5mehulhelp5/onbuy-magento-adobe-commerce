<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

use M2E\OnBuy\Model\Listing;

class Request extends \M2E\OnBuy\Model\Product\Action\AbstractRequest
{
    use \M2E\OnBuy\Model\Product\Action\RequestTrait;

    public const LISTING_MODE = 'listing';
    public const PRODUCT_MODE = 'product';

    private array $metadata = [];

    public function getActionData(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
        $dataProvider = $product->getDataProvider();
        $priceData = $dataProvider->getPrice()->getValue();

        if ($this->getActionMode($product) === self::LISTING_MODE) {
            $request = $this->getActionDataForListingMode($product);
        } else {
            $request = $this->getActionDataForProductMode($product);
        }

        $this->metadata = [
            'opc' => $product->getOpc(),
            'sku' => $product->getMagentoProduct()->getSku(),
            'group_sku' => null,
            'price' => $priceData->price,
            'qty' => $dataProvider->getQty()->getValue(),
        ];

        $this->processDataProviderLogs($dataProvider);

        return $request;
    }

    public function getActionDataForListingMode(\M2E\OnBuy\Model\Product $product): array
    {
        $dataProvider = $product->getDataProvider();
        $priceData = $dataProvider->getPrice()->getValue();

        $request = [
            'opc' => $product->getOpc(),
            'sku' => $product->getMagentoProduct()->getSku(),
            'group_sku' => null,
            'price' => $priceData->price,
            'qty' => $dataProvider->getQty()->getValue(),
            'condition' => $product->getListing()->getCondition(),
            'condition_notes' => [],
            'delivery_template_id' => $dataProvider->getDelivery()->getValue()
        ];

        if ($product->getListing()->getCondition() !== Listing::CONDITION_NEW) {
            $request['condition_notes'] = [
                $product->getListing()->getConditionNote(),
            ];
        }

        return $request;
    }

    public function getActionDataForProductMode(\M2E\OnBuy\Model\Product $product): array
    {
        $dataProvider = $product->getDataProvider();
        $priceData = $dataProvider->getPrice()->getValue();
        $attributes = $dataProvider->getProductAttributesData()->getValue();

        $request = [
            'sku' => $product->getMagentoProduct()->getSku(),
            'group_sku' => null,
            'price' => $priceData->price,
            'qty' => $dataProvider->getQty()->getValue(),
            'condition' => $product->getListing()->getCondition(),
            'condition_notes' => [],
            'delivery_template_id' => $dataProvider->getDelivery()->getValue(),
            'title' => $dataProvider->getTitle()->getValue(),
            'description' => $dataProvider->getDescription()->getValue()->description,
            'bullet_points' => [],
            'category_id' => $dataProvider->getCategoryData()->getValue(),
            'identifiers' => [$dataProvider->getIdentifier()->getValue()],
            'main_image' => $dataProvider->getImages()->getValue()->mainImage,

            'additional_images' => array_map(
                static function (\M2E\OnBuy\Model\Product\DataProvider\Images\Image $image) {
                    return $image->url;
                },
                $dataProvider->getImages()->getValue()->galleryImages
            ),

            'brand_name' => $dataProvider->getProductBrand()->getValue(),

            'attributes' => array_map(
                static function (\M2E\OnBuy\Model\Product\DataProvider\Attributes\Item $attribute) {
                    return [
                        'option_id' => $attribute->getValueId(),
                    ];
                },
                $attributes->items
            ),
        ];

        if ($product->getListing()->getCondition() !== Listing::CONDITION_NEW) {
            $request['condition_notes'] = [
                $product->getListing()->getConditionNote(),
            ];
        }

        return $request;
    }

    public function getActionMode(\M2E\OnBuy\Model\Product $product): string
    {
        if ($product->hasOpc()) {
            return self::LISTING_MODE;
        }

        return self::PRODUCT_MODE;
    }

    protected function getActionMetadata(): array
    {
        return $this->metadata;
    }
}
