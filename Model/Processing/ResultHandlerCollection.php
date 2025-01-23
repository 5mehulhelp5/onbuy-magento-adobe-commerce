<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Processing;

class ResultHandlerCollection
{
    private const MAP = [
        \M2E\OnBuy\Model\InventorySync\Processing\ResultHandler::NICK =>
            \M2E\OnBuy\Model\InventorySync\Processing\ResultHandler::class,
        \M2E\OnBuy\Model\Product\Action\Async\Processing\ResultHandler::NICK =>
            \M2E\OnBuy\Model\Product\Action\Async\Processing\ResultHandler::class,
    ];

    public function has(string $nick): bool
    {
        return isset(self::MAP[$nick]);
    }

    /**
     * @param string $nick
     *
     * @return string result handler class name
     */
    public function get(string $nick): string
    {
        return self::MAP[$nick];
    }
}
