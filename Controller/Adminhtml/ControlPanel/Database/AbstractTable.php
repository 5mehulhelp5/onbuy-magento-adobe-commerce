<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database;

use M2E\OnBuy\Controller\Adminhtml\ControlPanel\AbstractMain;

abstract class AbstractTable extends AbstractMain
{
    private \M2E\OnBuy\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\OnBuy\Helper\Module $moduleHelper,
        \M2E\OnBuy\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\OnBuy\Model\Module $module,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        parent::__construct($module);
        $this->databaseTableFactory = $databaseTableFactory;
        $this->cache = $cache;
    }

    protected function getTableModel(): \M2E\OnBuy\Model\ControlPanel\Database\TableModel
    {
        $tableName = $this->getRequest()->getParam('table');

        return $this->databaseTableFactory->create($tableName);
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
