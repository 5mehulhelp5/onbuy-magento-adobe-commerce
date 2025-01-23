<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\CancellationRequest;

class Decline
{
    public const REASON_INVALID_REASON = 'invalid_reason';
    public const REASON_DELIVERY_SCHEDULE = 'delivery_schedule';
    public const REASON_REACHED_AGREEMENT = 'reached_agreement';
    public const REASON_PRODUCT_PACKED = 'product_packed';

    public function process(\M2E\OnBuy\Model\Order $order, string $declineReason, int $initiator): bool
    {
        return true;
    }
}
