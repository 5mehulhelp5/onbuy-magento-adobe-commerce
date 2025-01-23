<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel;

class Product
{
    private int $accountId;
    private int $siteId;
    private int $channelProductId;
    private string $title;
    private string $productUrl;
    private string $sku;
    private ?string $groupSku;
    private string $opc;
    private string $productEncodedId;
    private array $identifiers;
    private float $price;
    private string $currencyCode;
    private int $handlingTime;
    private int $qty;
    private string $condition;
    private array $conditionNotes;
    private int $deliveryWeight;
    private int $deliveryTemplateId;
    private string $createDate;
    private string $updateDate;

    public function __construct(
        int $accountId,
        int $siteId,
        int $channelProductId,
        string $title,
        string $productUrl,
        string $sku,
        ?string $groupSku,
        string $opc,
        string $productEncodedId,
        array $identifiers,
        float $price,
        string $currencyCode,
        int $handlingTime,
        int $qty,
        string $condition,
        array $conditionNotes,
        int $deliveryWeight,
        int $deliveryTemplateId,
        string $createDate,
        string $updateDate
    ) {
        $this->accountId          = $accountId;
        $this->siteId             = $siteId;
        $this->channelProductId   = $channelProductId;
        $this->title              = $title;
        $this->productUrl         = $productUrl;
        $this->sku                = $sku;
        $this->groupSku           = $groupSku;
        $this->opc                = $opc;
        $this->productEncodedId   = $productEncodedId;
        $this->identifiers        = $identifiers;
        $this->price              = $price;
        $this->currencyCode       = $currencyCode;
        $this->handlingTime       = $handlingTime;
        $this->qty                = $qty;
        $this->condition          = $condition;
        $this->conditionNotes     = $conditionNotes;
        $this->deliveryWeight     = $deliveryWeight;
        $this->deliveryTemplateId = $deliveryTemplateId;
        $this->createDate         = $createDate;
        $this->updateDate         = $updateDate;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getSiteId(): int
    {
        return $this->siteId;
    }

    public function getChannelProductId(): int
    {
        return $this->channelProductId;
    }

    public function getStatus(): int
    {
        if ($this->qty > 0) {
            return \M2E\OnBuy\Model\Product::STATUS_LISTED;
        }

        return \M2E\OnBuy\Model\Product::STATUS_INACTIVE;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getProductUrl(): string
    {
        return $this->productUrl;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getGroupSku(): ?string
    {
        return $this->groupSku;
    }

    public function getOpc(): string
    {
        return $this->opc;
    }

    public function getProductEncodedId(): string
    {
        return $this->productEncodedId;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getHandlingTime(): int
    {
        return $this->handlingTime;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function getConditionNotes(): array
    {
        return $this->conditionNotes;
    }

    public function getDeliveryWeight(): int
    {
        return $this->deliveryWeight;
    }

    public function getDeliveryTemplateId(): int
    {
        return $this->deliveryTemplateId;
    }

    public function getUpdateDate(): string
    {
        return $this->updateDate;
    }

    public function getCreateDate(): string
    {
        return $this->createDate;
    }
}
