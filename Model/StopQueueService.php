<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class StopQueueService
{
    /** @var \M2E\OnBuy\Model\StopQueue\Create */
    private StopQueue\Create $create;
    /** @var \M2E\OnBuy\Model\StopQueue\Delete */
    private StopQueue\Delete $delete;

    public function __construct(
        \M2E\OnBuy\Model\StopQueue\Create $create,
        \M2E\OnBuy\Model\StopQueue\Delete $delete
    ) {
        $this->create = $create;
        $this->delete = $delete;
    }

    public function add(\M2E\OnBuy\Model\Product $product): void
    {
        $this->create->process($product);
    }

    public function process(): void
    {
        $this->delete->process();
    }

    public function deleteOldProcessed(\DateTime $borderDate): void
    {
        $this->delete->clearOld($borderDate);
    }
}
