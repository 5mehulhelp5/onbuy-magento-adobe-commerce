<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\System\Processing\Simple;

class ProcessDataTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'processing/simple/process/data';

    private \M2E\OnBuy\Model\Processing\ProcessResult\Simple $processResultSimple;
    private \M2E\OnBuy\Model\Processing\Lock\ClearMissed $lockClearMissed;

    public function __construct(
        \M2E\OnBuy\Model\Processing\ProcessResult\Simple $processResultSimple,
        \M2E\OnBuy\Model\Processing\Lock\ClearMissed $lockClearMissed
    ) {
        $this->processResultSimple = $processResultSimple;
        $this->lockClearMissed = $lockClearMissed;
    }

    public function process($context): void
    {
        $this->processResultSimple->processExpired();

        $this->processResultSimple->processData();

        $this->lockClearMissed->process();
    }
}
