<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\CancellationRequest;

class Accept
{
    public function process(\M2E\OnBuy\Model\Order $order, int $initiator): bool
    {
        return true;
    }
}
