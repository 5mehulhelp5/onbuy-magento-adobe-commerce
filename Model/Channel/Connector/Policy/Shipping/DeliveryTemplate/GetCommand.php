<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Policy\Shipping\DeliveryTemplate;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private int $siteId;

    public function __construct(string $accountHash, int $siteId)
    {
        $this->accountHash = $accountHash;
        $this->siteId = $siteId;
    }

    public function getCommand(): array
    {
        return ['deliveryTemplate', 'get', 'entities'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection {
        $collection = new \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection();

        foreach ($response->getResponseData()['delivery_templates'] ?? [] as $templateData) {
            $collection->add(
                new \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate(
                    $templateData['id'],
                    $templateData['title'],
                    $templateData['is_default']
                )
            );
        }

        return $collection;
    }
}
