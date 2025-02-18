<?php

declare(strict_types=1);

namespace M2E\OnBuy\Helper\Module\Database;

class Structure
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    private \M2E\Core\Helper\Module\Database\Structure $coreDBStructureHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Core\Helper\Module\Database\Structure $coreDBStructureHelper
    ) {
        $this->objectManager = $objectManager;
        $this->coreDBStructureHelper = $coreDBStructureHelper;
    }

    public function isTableExists(string $tableName, bool $force = false): bool
    {
        return $this->coreDBStructureHelper->isTableExists($tableName, $force);
    }

    public function isTableStatusOk(string $tableName): bool
    {
        return $this->coreDBStructureHelper->isTableStatusOk($tableName);
    }

    public function isTableReady(string $tableName): bool
    {
        return $this->coreDBStructureHelper->isTableReady($tableName);
    }

    public function getCountOfRecords(string $tableName): int
    {
        return $this->coreDBStructureHelper->getCountOfRecords($tableName);
    }

    public function getDataLengthInMB(string $tableName): float
    {
        return $this->coreDBStructureHelper->getDataLengthInMB($tableName);
    }

    public function getModuleTablesInfo(): array
    {
        $tablesInfo = [];
        foreach (\M2E\OnBuy\Helper\Module\Database\Tables::getAllTables() as $currentTable) {
            $currentTableInfo = $this->getTableInfo($currentTable);
            if ($currentTableInfo !== null) {
                $tablesInfo[$currentTable] = $currentTableInfo;
            }
        }

        return $tablesInfo;
    }

    public function getTableInfo(string $tableName): ?array
    {
        return $this->coreDBStructureHelper->getTableInfo($tableName);
    }

    public function getColumnInfo(string $table, string $columnName): ?array
    {
        return $this->coreDBStructureHelper->getColumnInfo($table, $columnName);
    }

    public function getIdColumn(string $table): string
    {
        $tableModel = \M2E\OnBuy\Helper\Module\Database\Tables::getTableResourceModel($table);
        $tableModel = $this->objectManager->get($tableModel);

        return $tableModel->getIdFieldName();
    }

    public function isIdColumnAutoIncrement(string $table): bool
    {
        $idColumn = $this->getIdColumn($table);
        $columnInfo = $this->getColumnInfo($table, $idColumn);

        return isset($columnInfo['extra']) && strpos($columnInfo['extra'], 'increment') !== false;
    }

    public function getTableNameWithPrefix(string $tableName): string
    {
        return $this->coreDBStructureHelper->getTableNameWithPrefix($tableName);
    }

    public function getTableNameWithoutPrefix(string $tableName): string
    {
        return $this->coreDBStructureHelper->getTableNameWithoutPrefix($tableName);
    }
}
