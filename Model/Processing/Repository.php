<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Processing;

use M2E\OnBuy\Model\ResourceModel\Processing as ProcessingResource;
use M2E\OnBuy\Model\ResourceModel\Processing\PartialData as PartialDataResource;
use Magento\Framework\Data\Collection;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Processing\CollectionFactory $collectionFactory;
    private PartialDataFactory $partialDataFactory;
    private \M2E\OnBuy\Model\ResourceModel\Processing\PartialData\CollectionFactory $partialDataCollectionFactory;
    private \M2E\Core\Helper\Module\Database\Tables $tablesHelper;
    /** @var \M2E\OnBuy\Model\Processing\Lock\Repository */
    private Lock\Repository $lockRepository;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Processing\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Model\Processing\PartialDataFactory $partialDataFactory,
        \M2E\OnBuy\Model\ResourceModel\Processing\PartialData\CollectionFactory $partialDataCollectionFactory,
        \M2E\OnBuy\Model\Processing\Lock\Repository $lockRepository,
        \M2E\Core\Helper\Module\Database\Tables $tablesHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->partialDataFactory = $partialDataFactory;
        $this->partialDataCollectionFactory = $partialDataCollectionFactory;
        $this->tablesHelper = $tablesHelper;
        $this->lockRepository = $lockRepository;
    }

    public function create(\M2E\OnBuy\Model\Processing $processing): void
    {
        $processing->save();
    }

    public function save(\M2E\OnBuy\Model\Processing $processing): void
    {
        $processing->save();
    }

    public function remove(\M2E\OnBuy\Model\Processing $processing): void
    {
        if ($processing->isTypePartial()) {
            $this->removePartialData($processing);
        }

        $processing->delete();
    }

    private function removePartialData(\M2E\OnBuy\Model\Processing $processing): void
    {
        $collectionPartial = $this->partialDataCollectionFactory->create();
        $collectionPartial->getConnection()->delete(
            $this->tablesHelper->getFullName(
                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_PARTIAL_DATA,
            ),
            [sprintf('`%s` = ?', PartialDataResource::COLUMN_PROCESSING_ID) => $processing->getId()],
        );
    }

    public function forceRemove(\M2E\OnBuy\Model\Processing $processing): void
    {
        $this->lockRepository->removeAllByProcessing($processing);
        $this->remove($processing);
    }

    public function createPartialData(\M2E\OnBuy\Model\Processing $processing, int $partNumber, array $data): void
    {
        $part = $this->partialDataFactory->create();
        $part->create($processing, $data, $partNumber);

        $part->save();
    }

    /**
     * @param \M2E\OnBuy\Model\Processing $processing
     *
     * @return \M2E\OnBuy\Model\Processing\PartialData[]
     */
    public function getPartialData(\M2E\OnBuy\Model\Processing $processing): array
    {
        $collectionPartial = $this->partialDataCollectionFactory->create();
        $collectionPartial->addFieldToFilter(PartialDataResource::COLUMN_PROCESSING_ID, $processing->getId());

        return array_values($collectionPartial->getItems());
    }

    /**
     * @param \DateTime $borderDate
     *
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findPartialForDownloadData(\DateTime $borderDate): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ProcessingResource::COLUMN_TYPE,
                \M2E\OnBuy\Model\Processing::TYPE_PARTIAL
            )
            ->addFieldToFilter(
                ProcessingResource::COLUMN_CREATE_DATE,
                ['lteq' => $borderDate->format('Y-m-d H:i:s')]
            )
            ->addFieldToFilter(
                ProcessingResource::COLUMN_STAGE,
                [
                    'in' => [
                        \M2E\OnBuy\Model\Processing::STAGE_WAIT_SERVER,
                        \M2E\OnBuy\Model\Processing::STAGE_DOWNLOAD,
                    ],
                ],
            )
            ->addFieldToFilter(ProcessingResource::COLUMN_IS_COMPLETED, 0)
            ->setOrder(ProcessingResource::COLUMN_CREATE_DATE, Collection::SORT_ORDER_ASC);

        return array_values($collection->getItems());
    }

    /**
     * @param int $limit
     *
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findPartialTypeForProcess(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ProcessingResource::COLUMN_TYPE,
                \M2E\OnBuy\Model\Processing::TYPE_PARTIAL
            )
            ->addFieldToFilter(
                ProcessingResource::COLUMN_STAGE,
                \M2E\OnBuy\Model\Processing::STAGE_WAIT_PROCESS
            )
            ->addFieldToFilter(
                ProcessingResource::COLUMN_IS_COMPLETED,
                0
            )
            ->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findPartialTypeExpired(): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ProcessingResource::COLUMN_TYPE,
                \M2E\OnBuy\Model\Processing::TYPE_PARTIAL
            )
            ->addFieldToFilter(
                ProcessingResource::COLUMN_EXPIRATION_DATE,
                ['lt' => \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')],
            )
            ->addFieldToFilter(
                ProcessingResource::COLUMN_STAGE,
                [
                    'in' => [
                        \M2E\OnBuy\Model\Processing::STAGE_WAIT_SERVER,
                        \M2E\OnBuy\Model\Processing::STAGE_DOWNLOAD,
                    ],
                ],
            )
            ->addFieldToFilter(ProcessingResource::COLUMN_IS_COMPLETED, 0);

        return array_values($collection->getItems());
    }

    /**
     * @param \DateTime $borderDate
     *
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findSimpleForDownloadData(\DateTime $borderDate): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\OnBuy\Model\Processing::TYPE_SIMPLE)
            ->addFieldToFilter('create_date', ['lteq' => $borderDate->format('Y-m-d H:i:s')])
            ->addFieldToFilter(
                'stage',
                [
                    'in' => [
                        \M2E\OnBuy\Model\Processing::STAGE_WAIT_SERVER,
                    ],
                ],
            )
            ->addFieldToFilter('is_completed', 0)
            ->setOrder('create_date', Collection::SORT_ORDER_ASC);

        return array_values($collection->getItems());
    }

    /**
     * @param int $limit
     *
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findSimpleTypeForProcess(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\OnBuy\Model\Processing::TYPE_SIMPLE)
            ->addFieldToFilter('stage', \M2E\OnBuy\Model\Processing::STAGE_WAIT_PROCESS)
            ->addFieldToFilter('is_completed', 0)
            ->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findSimpleTypeExpired(): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\OnBuy\Model\Processing::TYPE_SIMPLE)
            ->addFieldToFilter(
                'expiration_date',
                ['lt' => \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')],
            )
            ->addFieldToFilter(
                'stage',
                [
                    'in' => [
                        \M2E\OnBuy\Model\Processing::STAGE_WAIT_SERVER,
                        \M2E\OnBuy\Model\Processing::STAGE_DOWNLOAD,
                    ],
                ],
            )
            ->addFieldToFilter('is_completed', 0);

        return array_values($collection->getItems());
    }

    /**
     * @param int[] $ids
     *
     * @return \M2E\OnBuy\Model\Processing[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ProcessingResource::COLUMN_ID, ['in' => array_unique($ids)]);

        return array_values($collection->getItems());
    }
}
