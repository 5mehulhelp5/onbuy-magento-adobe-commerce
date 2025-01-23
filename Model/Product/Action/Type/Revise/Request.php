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
            'qty' => $request['qty'],

        ];

        $this->processDataProviderLogs($dataProvider);

        return $request;
    }

    protected function getActionMetadata(): array
    {
        return $this->metadata;
    }
}
