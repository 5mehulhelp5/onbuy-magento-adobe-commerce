<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

class Request extends \M2E\OnBuy\Model\Product\Action\AbstractRequest
{
    use \M2E\OnBuy\Model\Product\Action\RequestTrait;

    private array $metadata = [];

    public function getActionData(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
        $dataProvider = $product->getDataProvider();
        $priceData = $dataProvider->getPrice()->getValue();

        $request = [
            'sku' => $product->getOnlineSku(),
            'price' => $priceData->price,
            'qty' => $dataProvider->getQty()->getValue(),
        ];

        $this->metadata = [
            'price' => $request['price'],
            'qty' => $request['qty']
        ];

        if ($dataProvider->getDelivery()->getValue() !== null) {
            $deliveryTemplateId = $dataProvider->getDelivery()->getValue();
            $request['delivery_template_id'] = $deliveryTemplateId;
            $this->metadata['delivery_template_id'] = $deliveryTemplateId;
        }

        if ($actionConfigurator->isShippingAllowed()) {
            $handlingTime = $dataProvider->getHandlingTime()->getValue();
            $request['handling_time'] = $handlingTime;
            $this->metadata['handling_time'] = $handlingTime;
        }

        if ($product->isProductCreator() && $actionConfigurator->isDetailsAllowed()) {
            $attributes = $dataProvider->getProductAttributesData()->getValue();
            $request['opc'] = $product->getOpc();
            $request['title'] = $dataProvider->getTitle()->getValue();
            $request['description'] = $dataProvider->getDescription()->getValue()->description;
            $request['category_id'] = $dataProvider->getCategoryData()->getValue();
            $request['main_image'] = $dataProvider->getImages()->getValue()->mainImage;
            $request['additional_images'] = array_map(
                static function (\M2E\OnBuy\Model\Product\DataProvider\Images\Image $image) {
                    return $image->url;
                },
                $dataProvider->getImages()->getValue()->galleryImages
            );

            $request['attributes'] = array_map(
                static function (\M2E\OnBuy\Model\Product\DataProvider\Attributes\Item $attribute) {
                    return [
                        'option_id' => $attribute->getValueId(),
                    ];
                },
                $attributes->items
            );

            $this->metadata['details']['title'] = $request['title'];
            $this->metadata['details']['description_hash'] = $dataProvider->getDescription()->getValue()->hash;
            $this->metadata['details']['category_id'] = $request['category_id'];
            $this->metadata['details']['attributes_hash'] = $attributes->hash;
            $this->metadata['details']['main_image'] = $request['main_image'];
            $this->metadata['details']['additional_images_hash'] =  $dataProvider->getImages()->getValue()->hashGalleryImages;
        }

        $this->processDataProviderLogs($dataProvider);

        return $request;
    }

    protected function getActionMetadata(): array
    {
        return $this->metadata;
    }
}
