<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class Item
{
    private string $name;
    private string $sku;
    private int $qty;
    private string $channelProductId; // opc
    private int $qtyDispatched;
    private Item\Price $price;
    private \DateTimeImmutable $expectedDispatchDate;
    private array $fee;
    private Item\Tax $tax;
    private ?Item\Tracking $tracking;

    public function __construct(
        string $name,
        string $sku,
        string $channelProductId,
        int $qty,
        int $qtyDispatched,
        Item\Price $price,
        \DateTimeImmutable $expectedDispatchDate,
        array $fee,
        Item\Tax $tax,
        ?Item\Tracking $tracking
    ) {
        $this->name = $name;
        $this->sku = $sku;
        $this->qty = $qty;
        $this->channelProductId = $channelProductId;
        $this->qtyDispatched = $qtyDispatched;
        $this->price = $price;
        $this->expectedDispatchDate = $expectedDispatchDate;
        $this->fee = $fee;
        $this->tax = $tax;
        $this->tracking = $tracking;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getChannelProductId(): string
    {
        return $this->channelProductId;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getQtyDispatched(): int
    {
        return $this->qtyDispatched;
    }

    public function getPrice(): Item\Price
    {
        return $this->price;
    }

    public function getExpectedDispatchDate(): \DateTimeImmutable
    {
        return $this->expectedDispatchDate;
    }

    public function getFee(): array
    {
        return $this->fee;
    }

    public function getTax(): Item\Tax
    {
        return $this->tax;
    }

    public function hasTracking(): bool
    {
        return $this->tracking !== null;
    }

    public function getTracking(): ?Item\Tracking
    {
        return $this->tracking;
    }
}
