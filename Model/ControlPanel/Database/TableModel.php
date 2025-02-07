<?php

namespace M2E\OnBuy\Model\ControlPanel\Database;

use M2E\OnBuy\Model\Exception;

class TableModel
{
    private string $tableName;
    private string $modelName;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper;
    private \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection,
        \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        string $tableName
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->tableName = $tableName;
        $this->collection = $collection;
    }

    public function getCollection(): \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    {
        return $this->collection;
    }

    public function getColumns()
    {
        return $this->dbStructureHelper->getTableInfo($this->createModel()->getResource()->getMainTable());
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function createModel(): \Magento\Framework\DataObject
    {
        return $this->getCollection()->getNewEmptyItem();
    }

    public function createEntry(array $data): void
    {
        $modelInstance = $this->createModel();

        $idFieldName = $modelInstance->getIdFieldName();
        $isIdAutoIncrement = $this->isIdColumnAutoIncrement();
        if ($isIdAutoIncrement) {
            unset($data[$idFieldName]);
        }

        $modelInstance->setData($data);

        $modelInstance->getResource()
                      ->save($modelInstance);
    }

    public function deleteEntries(array $ids): void
    {
        $modelInstance = $this->createModel();
        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), ['in' => $ids]);

        foreach ($collection as $item) {
            $item->getResource()->delete($item);
        }
    }

    public function updateEntries(array $ids, array $data): void
    {
        $modelInstance = $this->createModel();

        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), ['in' => $ids]);

        $idFieldName = $modelInstance->getIdFieldName();
        $isIdAutoIncrement = $this->isIdColumnAutoIncrement();
        if ($isIdAutoIncrement) {
            unset($data[$idFieldName]);
        }

        if (empty($data)) {
            return;
        }

        foreach ($collection->getItems() as $item) {
            /** @var \M2E\OnBuy\Model\ActiveRecord\AbstractModel $item */

            foreach ($data as $field => $value) {
                if ($field === $idFieldName && !$isIdAutoIncrement) {
                    $this->resourceConnection->getConnection()->update(
                        $this->dbStructureHelper->getTableNameWithPrefix($this->tableName),
                        [$idFieldName => $value],
                        "`$idFieldName` = {$item->getId()}"
                    );
                }

                $item->setData($field, $value);
            }

            $item->getResource()->save($item);
        }
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    private function isIdColumnAutoIncrement(): bool
    {
        $list = [
            'setup' => true,
            'm2e_core_config' => true,
            'm2e_core_registry' => true,
        ];

        return $list[$this->tableName] ?? $this->dbStructureHelper->isIdColumnAutoIncrement($this->tableName);
    }
}
