<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\View\OnBuy;

class Row extends \Magento\Catalog\Model\Product
{
    public const KEY_LISTING_PRODUCT_ID = 'id';
    public const KEY_LISTING_PRODUCT = 'listing_product';

    public function getListingProductId(): int
    {
        return (int)$this->getData(self::KEY_LISTING_PRODUCT_ID);
    }

    public function setListingProduct(?\M2E\OnBuy\Model\Product $product)
    {
        $this->setData(self::KEY_LISTING_PRODUCT, $product);
    }

    public function getListingProduct(): ?\M2E\OnBuy\Model\Product
    {
        return $this->getData(self::KEY_LISTING_PRODUCT);
    }
}
