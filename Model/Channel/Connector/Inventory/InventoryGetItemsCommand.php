<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Inventory;

class InventoryGetItemsCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
{
    private string $accountServerHash;

    private int $siteId;

    public function __construct(
        string $accountServerHash,
        int $siteId
    ) {
        $this->accountServerHash = $accountServerHash;
        $this->siteId = $siteId;
    }

    public function getCommand(): array
    {
        return ['inventory', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'site_id' => $this->siteId,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
