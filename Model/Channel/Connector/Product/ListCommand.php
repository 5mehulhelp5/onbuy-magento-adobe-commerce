<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Product;

class ListCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
{
    private string $accountHash;

    private int $siteId;
    private array $requestData;

    private string $mode;

    public function __construct(
        string $accountHash,
        int $siteId,
        array $requestData,
        string $mode
    ) {
        $this->accountHash = $accountHash;
        $this->siteId = $siteId;
        $this->requestData = $requestData;
        $this->mode = $mode;
    }

    public function getCommand(): array
    {
        return ['product', 'create', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId,
            'product' => $this->requestData,
            'mode' => $this->mode,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
