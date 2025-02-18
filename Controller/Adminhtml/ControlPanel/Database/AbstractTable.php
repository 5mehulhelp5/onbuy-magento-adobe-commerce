<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database;

abstract class AbstractTable extends \M2E\OnBuy\Controller\Adminhtml\ControlPanel\AbstractMain
{
    private \M2E\Core\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\Core\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        parent::__construct();
        $this->databaseTableFactory = $databaseTableFactory;
        $this->cache = $cache;
    }

    protected function getTableModel(): \M2E\Core\Model\ControlPanel\Database\TableModel
    {
        return $this->databaseTableFactory->createFromRequest();
    }

    protected function prepareCellsValuesArray(): array
    {
        $cells = $this->getRequest()->getParam('cells', []);
        if (is_string($cells)) {
            $cells = [$cells];
        }

        $bindArray = [];
        foreach ($cells as $columnName) {
            $columnValue = $this->getRequest()->getParam('value_' . $columnName);

            if ($columnValue === null) {
                continue;
            }

            if (strtolower($columnValue) === 'null') {
                $columnValue = null;
            }

            $bindArray[$columnName] = $columnValue;
        }

        return $bindArray;
    }

    protected function prepareIds(): array
    {
        $ids = explode(',', $this->getRequest()->getParam('ids'));

        return array_filter(array_map('intval', $ids));
    }

    protected function redirectToTablePage(string $tableName): void
    {
        $this->_redirect('*/*/manageTable', ['table' => $tableName]);
    }

    protected function afterTableAction(string $tableName): void
    {
        if (
            strpos($tableName, 'config') !== false
            || strpos($tableName, 'wizard') !== false
        ) {
            $this->cache->removeAllValues();
        }
    }
}
