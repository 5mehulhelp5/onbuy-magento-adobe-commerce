<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Model\ResourceModel\StopQueue as ResourceModel;

class StopQueue extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
    }

    public function create(int $accountId, int $siteId, string $onlineSku): self
    {
        return $this->setAccountId($accountId)
                    ->setSiteId($siteId)
                    ->setOnlineSku($onlineSku);
    }

    public function setAsProcessed(): void
    {
        $this->setData(ResourceModel::COLUMN_IS_PROCESSED, 1);
    }

    public function setAccountId(int $accountId): self
    {
        return $this->setData(ResourceModel::COLUMN_ACCOUNT_ID, $accountId);
    }

    public function setSiteId(int $siteId): self
    {
        return $this->setData(ResourceModel::COLUMN_SITE_ID, $siteId);
    }

    public function setOnlineSku(string $onlineSku): self
    {
        return $this->setData(ResourceModel::COLUMN_SKU, $onlineSku);
    }
}
