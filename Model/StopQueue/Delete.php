<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\StopQueue;

class Delete
{
    private const MAXIMUM_PRODUCTS_PER_REQUEST = 200;

    private \M2E\OnBuy\Model\StopQueue\Repository $repository;

    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\OnBuy\Model\StopQueue\Repository $repository,
        \M2E\OnBuy\Model\Connector\Client\Single $serverClient
    ) {
        $this->repository = $repository;
        $this->serverClient = $serverClient;
    }

    public function process(): void
    {
        foreach ($this->repository->getGroupedAccountAndSite() as $row) {
            $skusToDelete = $this->repository->getSkusByAccountSite(
                (int)$row['account_id'],
                (int)$row['site_id'],
                self::MAXIMUM_PRODUCTS_PER_REQUEST
            );

            if (!empty($skusToDelete)) {
                $command = new \M2E\OnBuy\Model\Channel\Connector\Product\BulkDeleteCommand(
                    $row['server_hash'],
                    (int)$row['channel_site_id'],
                    $skusToDelete
                );

                $response = $this->serverClient->process($command);

                if ($response->isResultSuccess() && empty($response->getResponseData())) {
                    $this->repository->massStatusUpdate(
                        $skusToDelete,
                        (int)$row['account_id'],
                        (int)$row['site_id']
                    );
                }
            }
        }
    }

    public function clearOld(\DateTime $borderDate): void
    {
        $this->repository->deleteCompletedAfterBorderDate($borderDate);
    }
}
