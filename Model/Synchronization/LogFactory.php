<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Synchronization;

class LogFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Log
    {
        return $this->objectManager->create(Log::class);
    }

    public function create(
        int $initiator,
        int $task,
        ?int $operationHistoryId,
        string $description,
        int $type,
        ?string $detailedDescription = null
    ): Log {
        $obj = $this->createEmpty();

        $obj->create(
            $initiator,
            $task,
            $operationHistoryId,
            $description,
            $type,
            $detailedDescription
        );

        return $obj;
    }
}
