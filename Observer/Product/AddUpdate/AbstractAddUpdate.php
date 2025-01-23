<?php

namespace M2E\OnBuy\Observer\Product\AddUpdate;

abstract class AbstractAddUpdate extends \M2E\OnBuy\Observer\Product\AbstractProduct
{
    private ?\M2E\OnBuy\Model\Product\AffectedProduct\Collection $affectedProductCollection = null;
    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory
    ) {
        parent::__construct(
            $productFactory,
            $ourMagentoProductFactory
        );
        $this->listingProductRepository = $listingProductRepository;
    }

    /**
     * @return bool
     */
    public function canProcess(): bool
    {
        return ((string)$this->getEvent()->getProduct()->getSku()) !== '';
    }

    //########################################

    abstract protected function isAddingProductProcess();

    //########################################

    protected function areThereAffectedItems(): bool
    {
        return !$this->getAffectedProductCollection()->isEmpty();
    }

    // ---------------------------------------

    protected function getAffectedProductCollection(): \M2E\OnBuy\Model\Product\AffectedProduct\Collection
    {
        if ($this->affectedProductCollection !== null) {
            return $this->affectedProductCollection;
        }

        return $this->affectedProductCollection = $this->listingProductRepository
            ->getProductsByMagentoProductId($this->getProductId());
    }
}
