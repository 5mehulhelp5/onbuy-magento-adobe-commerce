<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Instruction\SynchronizationTemplate\Checker;

class Input extends \M2E\OnBuy\Model\Instruction\Handler\Input
{
    private \M2E\OnBuy\Model\ScheduledAction $scheduledAction;

    public function setScheduledAction(\M2E\OnBuy\Model\ScheduledAction $scheduledAction): self
    {
        $this->scheduledAction = $scheduledAction;

        return $this;
    }

    public function getScheduledAction(): ?\M2E\OnBuy\Model\ScheduledAction
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->scheduledAction ?? null;
    }
}
