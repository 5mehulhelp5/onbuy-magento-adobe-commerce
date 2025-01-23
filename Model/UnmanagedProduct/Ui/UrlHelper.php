<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct\Ui;

class UrlHelper
{
    private const PATH_GRID_UNMANAGED = 'm2e_onbuy/product_grid/unmanaged';
    private const PATH_UNMANAGED_PRODUCT_POPUP = 'm2e_onbuy/product_unmanaged_mapping/mapProductPopupHtml';
    private const PATH_UNMANAGED_MAP = 'm2e_onbuy/product_unmanaged_mapping/map';
    public const PATH_UNMANAGED_MAP_GRID = 'm2e_onbuy/product_unmanaged_mapping/mapGrid';
    private const PATH_UNMANAGED_PREPARE_MOVE_TO_LISTING = 'm2e_onbuy/product_unmanaged_moving/prepareMoveToListing';
    public const PATH_UNMANAGED_MOVE_TO_LISTING = 'm2e_onbuy/product_unmanaged_moving/MoveToListingGrid';
    private const PATH_UNMANAGED_RESET = 'm2e_onbuy/product_unmanaged/reset';

    private \Magento\Framework\UrlInterface $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function getGridUrl(array $params = []): string
    {
        return $this->urlBuilder->getUrl(self::PATH_GRID_UNMANAGED, $params);
    }

    public function getUnmanagedProductPopupUrl(array $params = []): string
    {
        return $this->urlBuilder->getUrl(self::PATH_UNMANAGED_PRODUCT_POPUP, $params);
    }

    public function getUnmanagedMapUrl(array $params = []): string
    {
        return $this->urlBuilder->getUrl(self::PATH_UNMANAGED_MAP, $params);
    }

    public function getUnmanagedPrepareMoveToListingUrl(): string
    {
        return $this->urlBuilder->getUrl(self::PATH_UNMANAGED_PREPARE_MOVE_TO_LISTING);
    }

    public function getUnmanagedMoveToListingUrl(): string
    {
        return $this->urlBuilder->getUrl(self::PATH_UNMANAGED_MOVE_TO_LISTING);
    }

    public function getUnmanagedResetUrl(array $params = []): string
    {
        return $this->urlBuilder->getUrl(self::PATH_UNMANAGED_RESET, $params);
    }
}
