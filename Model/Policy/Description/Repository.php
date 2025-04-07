<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Description;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Policy\Description $resource;
    private \M2E\OnBuy\Model\Policy\DescriptionFactory $descriptionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Policy\Description\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Policy\Description $resource,
        \M2E\OnBuy\Model\ResourceModel\Policy\Description\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Model\Policy\DescriptionFactory $descriptionFactory
    ) {
        $this->resource = $resource;
        $this->descriptionFactory = $descriptionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Policy\Description
    {
        $model = $this->descriptionFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function get(int $id): \M2E\OnBuy\Model\Policy\Description
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Description not found');
        }

        return $template;
    }

    public function delete(\M2E\OnBuy\Model\Policy\Description $template): void
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\OnBuy\Model\Policy\Description $template): void
    {
        $this->resource->save($template);
    }

    public function save(\M2E\OnBuy\Model\Policy\Description $template): void
    {
        $this->resource->save($template);
    }

    /**
     * @return \M2E\OnBuy\Model\Policy\Description[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
