<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Product;

class BulkDeleteCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private int $siteId;
    private array $requestData;

    public function __construct(
        string $accountHash,
        int $siteId,
        array $requestData
    ) {
        $this->accountHash = $accountHash;
        $this->siteId = $siteId;
        $this->requestData = $requestData;
    }

    public function getCommand(): array
    {
        return ['product', 'delete', 'entities'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId,
            'product_skus' => $this->requestData,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
