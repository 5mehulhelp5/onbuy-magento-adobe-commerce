<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

use M2E\OnBuy\Model\Listing;

class Request extends \M2E\OnBuy\Model\Product\Action\AbstractRequest
{
    use \M2E\OnBuy\Model\Product\Action\RequestTrait;

    public const LISTING_MODE = 'listing';

    private array $metadata = [];

    public function getActionData(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
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

    protected function getActionMetadata(): array
    {
        return $this->metadata;
    }
}
