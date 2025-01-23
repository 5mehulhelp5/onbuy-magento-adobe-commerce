<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database;

class ManageTable extends AbstractTable
{
    protected \M2E\OnBuy\Helper\View\ControlPanel $controlPanelHelper;

    public function __construct(
        \M2E\OnBuy\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\OnBuy\Helper\Module $moduleHelper,
        \M2E\OnBuy\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\OnBuy\Model\Module $module,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module, $cache);
        $this->controlPanelHelper = $controlPanelHelper;
    }

    public function execute()
    {
        $this->init();
        $table = $this->getRequest()->getParam('table');

        if ($table === null) {
            return $this->_redirect($this->controlPanelHelper->getPageDatabaseTabUrl());
        }

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs\Database\Table::class,
                '',
                ['tableName' => $table],
            ),
        );

        return $this->getResultPage();
    }
}
