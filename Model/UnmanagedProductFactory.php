<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class UnmanagedProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): UnmanagedProduct
    {
        return $this->objectManager->create(UnmanagedProduct::class);
    }

    public function createFromChannel(\M2E\OnBuy\Model\Channel\Product $channelProduct): UnmanagedProduct
    {
        return $this->create(
            $channelProduct->getAccountId(),
            $channelProduct->getSiteId(),
            $channelProduct->getChannelProductId(),
            $channelProduct->getStatus(),
            $channelProduct->getTitle(),
            $channelProduct->getProductUrl(),
            $channelProduct->getSku(),
            $channelProduct->getGroupSku(),
            $channelProduct->getOpc(),
            $channelProduct->getProductEncodedId(),
            $channelProduct->getIdentifiers(),
            $channelProduct->getPrice(),
            $channelProduct->getCurrencyCode(),
            $channelProduct->getHandlingTime(),
            $channelProduct->getQty(),
            $channelProduct->getCondition(),
            $channelProduct->getConditionNotes(),
            $channelProduct->getDeliveryWeight(),
            $channelProduct->getDeliveryTemplateId(),
        );
    }

    private function create(
        int $accountId,
        int $siteId,
        int $channelProductId,
        int $status,
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
        int $deliveryTemplateId
    ): UnmanagedProduct {
        $object = $this->createEmpty();

        $object->create(
            $accountId,
            $siteId,
            $channelProductId,
            $status,
            $title,
            $productUrl,
            $sku,
            $groupSku,
            $opc,
            $productEncodedId,
            $identifiers,
            $price,
            $currencyCode,
            $handlingTime,
            $qty,
            $condition,
            $conditionNotes,
            $deliveryWeight,
            $deliveryTemplateId
        );

        return $object;
    }
}
