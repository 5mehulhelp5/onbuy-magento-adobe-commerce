<?php

namespace M2E\OnBuy\Model\Magento\Product;

class Status
{
    protected $resourceModel;
    protected $productResource;
    protected $magentoProductCollectionFactory;

    protected $_productAttributes = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceModel,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \M2E\Core\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->productResource = $productResource;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
    }

    protected function _getProductAttribute($attribute)
    {
        if (empty($this->_productAttributes[$attribute])) {
            $this->_productAttributes[$attribute] = $this->productResource->getAttribute($attribute);
        }

        return $this->_productAttributes[$attribute];
    }

    protected function _getReadAdapter()
    {
        return $this->resourceModel->getConnection();
    }

    public function getProductStatus($productIds, $storeId = null)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $collection = $this->magentoProductCollectionFactory->create();
        $collection->addFieldToFilter([
            ['attribute' => 'entity_id', 'in' => $productIds],
        ]);
        $collection->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            (int)$storeId
        );

        $rows = [];
        $queryStmt = $collection->getSelect()->query();

        while ($row = $queryStmt->fetch()) {
            $rows[$row['entity_id']] = $row['status'];
        }

        $statuses = [];

        foreach ($productIds as $productId) {
            if (isset($rows[$productId])) {
                $statuses[$productId] = $rows[$productId];
            } else {
                $statuses[$productId] = -1;
            }
        }

        return $statuses;
    }
}
