<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database;

class ShowOperationHistoryExecutionTreeUp extends AbstractTable
{
    private \M2E\OnBuy\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\OnBuy\Model\OperationHistory\Repository $repository,
        \M2E\OnBuy\Helper\Module $moduleHelper,
        \M2E\OnBuy\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\OnBuy\Model\Module $module,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module, $cache);
        $this->repository = $repository;
    }

    public function execute()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->getMessageManager()->addErrorMessage('Operation history ID is not presented.');

            $this->redirectToTablePage(
                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
            );

            //exit
        }

        $operationHistory = $this->repository->get((int)$operationHistoryId);

        $this->getResponse()->setBody(
            '<pre>' . $operationHistory->getExecutionTreeUpInfo() . '</pre>',
        );
    }
}
