<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Delete;

class Request extends \M2E\OnBuy\Model\Product\Action\AbstractRequest
{
    public function getActionData(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
        return [
            'sku' => $product->getOnlineSku(),
        ];
    }

    protected function getActionMetadata(): array
    {
        return [];
    }
}
