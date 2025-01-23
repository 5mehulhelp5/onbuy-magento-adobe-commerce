<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Product;

class ReviseCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
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
        return ['product', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId,
            'product' => $this->requestData,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
