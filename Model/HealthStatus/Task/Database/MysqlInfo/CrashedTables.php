<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\HealthStatus\Task\Database\MysqlInfo;

use M2E\OnBuy\Model\HealthStatus\Task\IssueType;
use M2E\OnBuy\Model\HealthStatus\Task\Result as TaskResult;

class CrashedTables extends IssueType
{
    /** @var \M2E\OnBuy\Model\HealthStatus\Task\Result\Factory */
    private $resultFactory;
    private \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\OnBuy\Model\HealthStatus\Task\Result\Factory $resultFactory,
        \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        $this->resultFactory = $resultFactory;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function process(): TaskResult
    {
        $crashedTables = [];
        foreach (\M2E\OnBuy\Helper\Module\Database\Tables::getAllTables() as $tableName) {
            if (!$this->dbStructureHelper->isTableStatusOk($tableName)) {
                $crashedTables[] = $tableName;
            }
        }

        $result = $this->resultFactory->create($this);
        $result->setTaskData($crashedTables)
               ->setTaskMessage($this->getTaskMessage($crashedTables));

        $result->setTaskResult(empty($crashedTables) ? TaskResult::STATE_SUCCESS : TaskResult::STATE_CRITICAL);

        return $result;
    }

    private function getTaskMessage(array $crashedTables): string
    {
        if (empty($crashedTables)) {
            return '';
        }

        return implode(', ', $crashedTables);
    }
}
