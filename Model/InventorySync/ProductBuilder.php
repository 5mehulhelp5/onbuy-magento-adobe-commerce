<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync;

class ProductBuilder
{
    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Site $site;

    public function __construct(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ) {
        $this->account = $account;
        $this->site = $site;
    }

    public function build(array $channelRawProducts): \M2E\OnBuy\Model\Channel\Product\ProductCollection
    {
        $result = new \M2E\OnBuy\Model\Channel\Product\ProductCollection();
        foreach ($channelRawProducts as $channelRawProduct) {
            $channelProduct = new \M2E\OnBuy\Model\Channel\Product(
                $this->account->getId(),
                $this->site->getId(),
                (int)$channelRawProduct['id'],
                $channelRawProduct['name'],
                $channelRawProduct['product_url'],
                $channelRawProduct['sku'],
                $channelRawProduct['group_sku'],
                $channelRawProduct['opc'],
                $channelRawProduct['product_encoded_id'],
                $channelRawProduct['identifiers'] ?? [],
                $channelRawProduct['price'],
                $channelRawProduct['currency_code'],
                (int)$channelRawProduct['handling_time'],
                (int)$channelRawProduct['qty'],
                $channelRawProduct['condition'],
                $channelRawProduct['condition_notes'] ?? [],
                (int)$channelRawProduct['delivery_weight'],
                (int)$channelRawProduct['delivery_template_id'],
                $channelRawProduct['create_date'],
                $channelRawProduct['update_date']
            );

            $result->add($channelProduct);
        }

        return $result;
    }
}
