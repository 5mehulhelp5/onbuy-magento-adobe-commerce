<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class StatusResolver
{
    public static function resolve(string $channelStatus): int
    {
        $channelStatus = mb_strtolower($channelStatus);

        if ($channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_AWAITING_DISPATCH) {
            return \M2E\OnBuy\Model\Order::STATUS_UNSHIPPED;
        }

        if ($channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_PARTIALLY_DISPATCHED) {
            return \M2E\OnBuy\Model\Order::STATUS_PARTIALLY_SHIPPED;
        }

        if (
            $channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_DISPATCHED
            || $channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_COMPLETE
        ) {
            return \M2E\OnBuy\Model\Order::STATUS_SHIPPED;
        }

        if ($channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_PARTIALLY_REFUNDED) {
            return \M2E\OnBuy\Model\Order::STATUS_PARTIALLY_REFUNDED;
        }

        if (
            $channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_CANCELLED
            || $channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_CANCELLED_BY_BUYER
            || $channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_CANCELLED_BY_SELLER
        ) {
            return \M2E\OnBuy\Model\Order::STATUS_CANCELED;
        }

        if (
            $channelStatus === \M2E\OnBuy\Model\Channel\Order::STATUS_REFUNDED
        ) {
            return \M2E\OnBuy\Model\Order::STATUS_REFUNDED;
        }

        return \M2E\OnBuy\Model\Order::STATUS_UNKNOWN;
    }
}
