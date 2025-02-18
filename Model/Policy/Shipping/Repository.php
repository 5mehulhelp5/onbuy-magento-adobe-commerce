<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Shipping;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Policy\Shipping $resource;
    private \M2E\OnBuy\Model\Policy\ShippingFactory $shippingFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Policy\Shipping $resource,
        \M2E\OnBuy\Model\Policy\ShippingFactory $shippingFactory
    ) {
        $this->resource = $resource;
        $this->shippingFactory = $shippingFactory;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Policy\Shipping
    {
        $model = $this->shippingFactory->createEmpty();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function get(int $id): \M2E\OnBuy\Model\Policy\Shipping
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Shipping not found');
        }

        return $template;
    }

    public function delete(\M2E\OnBuy\Model\Policy\Shipping $template): void
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\OnBuy\Model\Policy\Shipping $template): void
    {
        $this->resource->save($template);
    }

    public function save(\M2E\OnBuy\Model\Policy\Shipping $template): void
    {
        $this->resource->save($template);
    }

    public function removeByAccountId(int $accountId): void
    {
        $deleteCondition = sprintf(
            '%s = ?',
            \M2E\OnBuy\Model\ResourceModel\Policy\Shipping::COLUMN_ACCOUNT_ID
        );

        $this->resource
            ->getConnection()
            ->delete($this->resource->getMainTable(), [$deleteCondition => $accountId]);
    }
}
