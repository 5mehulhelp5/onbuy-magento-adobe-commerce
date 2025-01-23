<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Get\Items;

class Response
{
    /** @var \M2E\OnBuy\Model\Channel\Order[] */
    private array $orders;
    private \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection;
    private \DateTimeImmutable $maxDateInResult;

    public function __construct(
        array $orders,
        \DateTimeImmutable $maxDateInResult,
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ) {
        $this->orders = $orders;
        $this->messageCollection = $messageCollection;
        $this->maxDateInResult = $maxDateInResult;
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Order[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getMessageCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }

    public function getMaxDateInResult(): \DateTimeImmutable
    {
        return $this->maxDateInResult;
    }
}
