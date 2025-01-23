<?php

namespace M2E\OnBuy\Model\Policy\Synchronization;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization $resource;
    private \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization\CollectionFactory $collectionFactory;
    private \M2E\OnBuy\Model\Policy\SynchronizationFactory $synchronizationFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization $resource,
        \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Model\Policy\SynchronizationFactory $synchronizationFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->synchronizationFactory = $synchronizationFactory;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Policy\Synchronization
    {
        $model = $this->synchronizationFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function get(int $id): \M2E\OnBuy\Model\Policy\Synchronization
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Synchronization policy does not exist.');
        }

        return $template;
    }

    public function delete(\M2E\OnBuy\Model\Policy\Synchronization $template)
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\OnBuy\Model\Policy\Synchronization $template)
    {
        $this->resource->save($template);
    }

    public function save(\M2E\OnBuy\Model\Policy\Synchronization $template)
    {
        $this->resource->save($template);
    }

    /**
     * @return \M2E\OnBuy\Model\Policy\Synchronization[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
