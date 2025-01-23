<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel;

class Order
{
    public const STATUS_AWAITING_DISPATCH = 'awaiting_dispatch';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CANCELLED_BY_SELLER = 'cancelled_by_seller';
    public const STATUS_CANCELLED_BY_BUYER = 'cancelled_by_buyer';
    public const STATUS_PARTIALLY_DISPATCHED = 'partially_dispatched';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    public const STATUS_REFUNDED = 'refunded';

    private string $orderId;
    private string $status;
    private Order\Price $price;
    private Order\SalesFee $salesFee;
    private string $currency;
    private Order\Tax $tax;
    private Order\Buyer $buyer;
    private Order\BillingAddress $billingAddress;
    private ?\DateTimeImmutable $shippedDate;
    private ?\DateTimeImmutable $cancelledDate;
    private \DateTimeImmutable $createDate;
    private \DateTimeImmutable $updateDate;
    private array $fee;
    /** @var \M2E\OnBuy\Model\Channel\Order\Item[] */
    private array $orderItems;
    /** @var \M2E\OnBuy\Model\Channel\Order\DeliveryAddress */
    private Order\DeliveryAddress $deliveryAddress;
    private ?string $stripeTransactionId;
    private ?string $paypalCaptureId;
    private string $deliveryService;

    /**
     * @param string $orderId
     * @param string $status
     * @param \M2E\OnBuy\Model\Channel\Order\Price $price
     * @param \M2E\OnBuy\Model\Channel\Order\SalesFee $salesFee
     * @param string $currency
     * @param \M2E\OnBuy\Model\Channel\Order\Tax $tax
     * @param \M2E\OnBuy\Model\Channel\Order\Buyer $buyer
     * @param \M2E\OnBuy\Model\Channel\Order\BillingAddress $billingAddress
     * @param \M2E\OnBuy\Model\Channel\Order\DeliveryAddress $deliveryAddress
     * @param string|null $stripeTransactionId
     * @param string|null $paypalCaptureId
     * @param string $deliveryService
     * @param \DateTimeImmutable|null $shippedDate
     * @param \DateTimeImmutable|null $cancelledDate
     * @param \DateTimeImmutable $createDate
     * @param \DateTimeImmutable $updateDate
     * @param array $fee
     * @param Order\Item[] $orderItems
     */
    public function __construct(
        string $orderId,
        string $status,
        Order\Price $price,
        Order\SalesFee $salesFee,
        string $currency,
        Order\Tax $tax,
        Order\Buyer $buyer,
        Order\BillingAddress $billingAddress,
        Order\DeliveryAddress $deliveryAddress,
        ?string $stripeTransactionId,
        ?string $paypalCaptureId,
        string $deliveryService,
        ?\DateTimeImmutable $shippedDate,
        ?\DateTimeImmutable $cancelledDate,
        \DateTimeImmutable $createDate,
        \DateTimeImmutable $updateDate,
        array $fee,
        array $orderItems
    ) {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->price = $price;
        $this->salesFee = $salesFee;
        $this->currency = $currency;
        $this->tax = $tax;
        $this->billingAddress = $billingAddress;
        $this->shippedDate = $shippedDate;
        $this->cancelledDate = $cancelledDate;
        $this->createDate = $createDate;
        $this->updateDate = $updateDate;
        $this->fee = $fee;
        $this->orderItems = $orderItems;
        $this->deliveryAddress = $deliveryAddress;
        $this->buyer = $buyer;
        $this->stripeTransactionId = $stripeTransactionId;
        $this->paypalCaptureId = $paypalCaptureId;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Order\Item[]
     */
    public function getOrderItems(): array
    {
        return $this->orderItems;
    }

    // ----------------------------------------

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPrice(): Order\Price
    {
        return $this->price;
    }

    public function getSalesFee(): Order\SalesFee
    {
        return $this->salesFee;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTaxDetails(): Order\Tax
    {
        return $this->tax;
    }

    public function getBuyer(): Order\Buyer
    {
        return $this->buyer;
    }

    public function getBillingAddress(): Order\BillingAddress
    {
        return $this->billingAddress;
    }

    public function getStripeTransactionId(): ?string
    {
        return $this->stripeTransactionId;
    }

    public function getPaypalCaptureId(): ?string
    {
        return $this->paypalCaptureId;
    }

    public function getDeliveryAddress(): Order\DeliveryAddress
    {
        return $this->deliveryAddress;
    }

    public function getDeliveryService(): string
    {
        return $this->deliveryService;
    }

    public function getShippedDate(): ?\DateTimeImmutable
    {
        return $this->shippedDate;
    }

    public function getCancelledDate(): ?\DateTimeImmutable
    {
        return $this->cancelledDate;
    }

    public function getCreateDate(): \DateTimeImmutable
    {
        return $this->createDate;
    }

    public function getUpdateDate(): \DateTimeImmutable
    {
        return $this->updateDate;
    }

    public function getFee(): array
    {
        return $this->fee;
    }
}
