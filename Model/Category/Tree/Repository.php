<?php

namespace M2E\OnBuy\Model\Category\Tree;

use M2E\OnBuy\Model\Category\Tree;
use M2E\OnBuy\Model\ResourceModel\Category\Tree as CategoryTreeResource;

class Repository
{
    private CategoryTreeResource\CollectionFactory $collectionFactory;
    /** @var \M2E\OnBuy\Model\ResourceModel\Category\Tree */
    private CategoryTreeResource $categoryTreeResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Category\Tree\CollectionFactory $collectionFactory,
        CategoryTreeResource $categoryTreeResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->categoryTreeResource = $categoryTreeResource;
    }

    /**
     * @return Tree[]
     */
    public function getRootCategories(int $siteId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SITE_ID,
            ['eq' => $siteId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['null' => true]
        );

        return array_values($collection->getItems());
    }

    public function getCategoryBySiteIdAndCategoryId(int $siteId, int $categoryId): ?Tree
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SITE_ID,
            ['eq' => $siteId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            ['eq' => $categoryId]
        );

        /** @var Tree $entity */
        $entity = $collection->getFirstItem();

        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    /**
     * @param int $siteId
     * @param int $parentCategoryId
     *
     * @return Tree[]
     */
    public function getChildCategories(int $siteId, int $parentCategoryId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SITE_ID,
            ['eq' => $siteId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['eq' => $parentCategoryId]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param Tree $entity
     *
     * @return Tree[]
     */
    public function getParents(Tree $entity): array
    {
        $ancestors = $this->getRecursivelyParents($entity);

        return array_reverse($ancestors);
    }

    /**
     * @param Tree[] $ancestors
     *
     * @return Tree[]
     */
    private function getRecursivelyParents(Tree $child, array $ancestors = []): array
    {
        if ($child->getParentCategoryId() === null) {
            return $ancestors;
        }

        $parent = $this->getCategoryBySiteIdAndCategoryId(
            $child->getSiteId(),
            $child->getParentCategoryId()
        );
        if ($parent === null) {
            return $ancestors;
        }

        $ancestors[] = $parent;

        return $this->getRecursivelyParents($parent, $ancestors);
    }

    /**
     * @param \M2E\OnBuy\Model\Category\Tree[] $categories
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function batchInsert(array $categories): void
    {
        $insertData = [];
        foreach ($categories as $category) {
            $insertData[] = [
                CategoryTreeResource::COLUMN_SITE_ID => $category->getSiteId(),
                CategoryTreeResource::COLUMN_CATEGORY_ID => $category->getCategoryId(),
                CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID => $category->getParentCategoryId(),
                CategoryTreeResource::COLUMN_TITLE => $category->getTitle(),
                CategoryTreeResource::COLUMN_IS_LEAF => $category->isLeaf(),
                CategoryTreeResource::COLUMN_PERMISSION_STATUSES => json_encode($category->getPermissionStatuses()),
            ];
        }

        $collection = $this->collectionFactory->create();
        $resource = $collection->getResource();

        foreach (array_chunk($insertData, 500) as $chunk) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $chunk);
        }
    }

    public function deleteBySiteId(int $siteId): void
    {
        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();
        $conditions = [
            sprintf('%s = %s', CategoryTreeResource::COLUMN_SITE_ID, $connection->quote($siteId))
        ];

        $connection->delete(
            $collection->getMainTable(),
            implode(' AND ', $conditions)
        );
    }

    /**
     * @return Tree[]
     */
    public function searchByTitleOrId(int $siteId, string $query, int $limit): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SITE_ID,
            ['eq' => $siteId]
        );

        $collection->addFieldToFilter(
            [CategoryTreeResource::COLUMN_TITLE, CategoryTreeResource::COLUMN_CATEGORY_ID],
            [['like' => "%$query%"], ['like' => "%$query%"]]
        );

        $collection->getSelect()->order([
            sprintf('%s DESC', CategoryTreeResource::COLUMN_IS_LEAF),
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
        ]);

        $collection->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return Tree[]
     */
    public function getChildren(int $siteId, int $parentCategoryId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['eq' => $parentCategoryId]
        );

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SITE_ID,
            ['eq' => $siteId]
        );

        $collection->getSelect()->order([
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
        ]);

        return array_values($collection->getItems());
    }

    public function categoryTreeExists(int $siteId): bool
    {
        $connection = $this->categoryTreeResource->getConnection();
        $tableName = $this->categoryTreeResource->getMainTable();

        $select = $connection->select()
                             ->from($tableName, [new \Zend_Db_Expr('1')])
                             ->where(CategoryTreeResource::COLUMN_SITE_ID . ' = :site_id')
                             ->limit(1);
        $bind = [':site_id' => $siteId];
        $result = $connection->fetchOne($select, $bind);

        return (bool)$result;
    }
}
