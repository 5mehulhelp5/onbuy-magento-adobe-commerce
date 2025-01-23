<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Change\ShippingProcessor;

class ChangeResult
{
    public bool $isSuccess;
    public bool $isSkipped;

    public string $trackingNumber;
    public string $trackingTitle;
    /** @var \M2E\Core\Model\Response\Message[] */
    public array $messages;
    /** @var \M2E\OnBuy\Model\Order\Item[] */
    public array $orderItems;

    private function __construct(
        bool $isSuccess,
        bool $isSkipped,
        array $messages,
        array $orderItems,
        string $trackingNumber,
        string $trackingTitle
    ) {
        $this->isSuccess = $isSuccess;
        $this->trackingNumber = $trackingNumber;
        $this->trackingTitle = $trackingTitle;
        $this->isSkipped = $isSkipped;
        $this->messages = $messages;
        $this->orderItems = $orderItems;
    }

    public static function createSkipped(): self
    {
        return new self(true, true, [], [], '', '');
    }

    public static function createSuccess(
        array $orderItems,
        string $trackingNumber,
        string $trackingTitle,
        array $messages
    ): self {
        return new self(true, false, $messages, $orderItems, $trackingNumber, $trackingTitle);
    }

    public static function createFailed(
        array $orderItems,
        string $trackingNumber,
        string $trackingTitle,
        array $messages
    ): self {
        return new self(false, false, $messages, $orderItems, $trackingNumber, $trackingTitle);
    }
}
