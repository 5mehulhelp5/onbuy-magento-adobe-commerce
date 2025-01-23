<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync\ReceivedProduct;

use M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct as ReceivedProductResource;

class Repository
{
    private ReceivedProductResource $resource;

    public function __construct(ReceivedProductResource $resource)
    {
        $this->resource = $resource;
    }

    public function create(\M2E\OnBuy\Model\InventorySync\ReceivedProduct $receivedProduct): void
    {
        $this->resource->save($receivedProduct);
    }

    /**
     * @param \M2E\OnBuy\Model\InventorySync\ReceivedProduct[] $receivedProducts
     *
     * @return void
     */
    public function createBatch(array $receivedProducts): void
    {
        $rows = [];

        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        foreach ($receivedProducts as $item) {
            $rows[] = [
                ReceivedProductResource::COLUMN_ACCOUNT_ID => $item->getAccountId(),
                ReceivedProductResource::COLUMN_SITE_ID => $item->getSiteId(),
                ReceivedProductResource::COLUMN_SKU => $item->getSku(),
                ReceivedProductResource::COLUMN_CREATE_DATE => $currentDate->format('Y-m-d H:i:s'),
            ];
        }

        if (empty($rows)) {
            return;
        }

        $this->resource
            ->getConnection()
            ->insertMultiple($this->resource->getMainTable(), $rows);
    }

    public function remove(\M2E\OnBuy\Model\InventorySync\ReceivedProduct $receivedProduct): void
    {
        $this->resource->delete($receivedProduct);
    }

    public function removeAllByAccountAndSite(int $accountId, int $siteId): void
    {
        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                [
                    ReceivedProductResource::COLUMN_ACCOUNT_ID . ' = ?' => $accountId,
                    ReceivedProductResource::COLUMN_SITE_ID . ' = ?' => $siteId,
                ]
            );
    }
}
