<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

class ActionCalculator
{
    private \M2E\OnBuy\Model\Magento\Product\RuleFactory $ruleFactory;

    public function __construct(
        \M2E\OnBuy\Model\Magento\Product\RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
    }

    public function calculate(\M2E\OnBuy\Model\Product $product, bool $force, int $change): Action
    {
        if ($product->isStatusNotListed()) {
            return $this->calculateToList($product);
        }

        if ($product->isStatusListed()) {
            return $this->calculateToReviseOrStop($product);
        }

        if ($product->isStatusInactive()) {
            return $this->calculateToRelist($product, $change);
        }

        return Action::createNothing($product);
    }

    //@todo To add correct logic
    public function calculateToList(\M2E\OnBuy\Model\Product $product): Action
    {
        if (
            !$product->isListable()
            || !$product->isStatusNotListed()
        ) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedListProduct($product)) {
            return Action::createNothing($product);
        }

        $configurator = new \M2E\OnBuy\Model\Product\Action\Configurator();
        $configurator->enableAll();

        return Action::createList($product, $configurator);
    }

    private function isNeedListProduct(\M2E\OnBuy\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isListMode()) {
            return false;
        }

        if (
            $syncPolicy->isListStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListWhenQtyCalculatedHasValue()
            && !$this->isProductHasCalculatedQtyForListRevise($product, (int)$syncPolicy->getListWhenQtyCalculatedHasValue())
        ) {
            return false;
        }

        if (
            $syncPolicy->isListAdvancedRulesEnabled()
            && !$this->isListAdvancedRuleMet($product, $syncPolicy)
        ) {
            return false;
        }

        return true;
    }

    private function isProductHasCalculatedQtyForListRevise(
        \M2E\OnBuy\Model\Product $product,
        int $minQty
    ): bool {
        $productQty = $product->getDataProvider()->getQty()->getValue();

        return $productQty >= $minQty;
    }

    private function isListAdvancedRuleMet(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Policy\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create()
            ->setData(
                [
                    'store_id' => $product->getListing()->getStoreId(),
                    'prefix' => \M2E\OnBuy\Model\Policy\Synchronization::LIST_ADVANCED_RULES_PREFIX,
                ],
            );
        $ruleModel->loadFromSerialized($syncPolicy->getListAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    public function calculateToReviseOrStop(\M2E\OnBuy\Model\Product $product): Action
    {
        if (
            !$product->isRevisable()
            && !$product->isStoppable()
        ) {
            return Action::createNothing($product);
        }

        if ($this->isNeedStopProduct($product)) {
            return Action::createStop($product);
        }

        $configurator = new \M2E\OnBuy\Model\Product\Action\Configurator();
        $configurator->disableAll();

        $this->updateConfiguratorAddQty(
            $configurator,
            $product
        );

        $this->updateConfiguratorAddPrice(
            $configurator,
            $product
        );

        $this->updateConfiguratorAddShipping(
            $configurator,
            $product
        );

        if ($product->isProductCreator()) {
            $this->updateConfiguratorAddDetails(
                $configurator,
                $product
            );
        }

        if (empty($configurator->getAllowedDataTypes())) {
            return Action::createNothing($product);
        }

        return Action::createRevise($product, $configurator);
    }

    private function isNeedStopProduct(\M2E\OnBuy\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isStopMode()) {
            return false;
        }

        if (
            $syncPolicy->isStopStatusDisabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopOutOfStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopWhenQtyCalculatedHasValue()
            && $this->isProductHasCalculatedQtyForStop($product, (int)$syncPolicy->getStopWhenQtyCalculatedHasValueMin())
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopAdvancedRulesEnabled()
            && $this->isStopAdvancedRuleMet($product, $syncPolicy)
        ) {
            return true;
        }

        return false;
    }

    private function isProductHasCalculatedQtyForStop(
        \M2E\OnBuy\Model\Product $product,
        int $minQty
    ): bool {
        $productQty = $product->getDataProvider()->getQty()->getValue();

        return $productQty <= $minQty;
    }

    private function isStopAdvancedRuleMet(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Policy\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create()
            ->setData(
                [
                    'store_id' => $product->getListing()->getStoreId(),
                    'prefix' => \M2E\OnBuy\Model\Policy\Synchronization::STOP_ADVANCED_RULES_PREFIX,
                ],
            );
        $ruleModel->loadFromSerialized($syncPolicy->getStopAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    private function updateConfiguratorAddQty(
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator,
        \M2E\OnBuy\Model\Product $product
    ): void {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (
            $syncPolicy->isReviseUpdateQty()
            && $this->isChangedQty($product, $syncPolicy)
        ) {
            $configurator->allowQty();
        }
    }

    private function isChangedQty(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Policy\Synchronization $syncPolicy
    ): bool {
        $maxAppliedValue = $syncPolicy->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $product->getDataProvider()->getQty()->getValue();
        $channelQty = $product->getOnlineQty();

        if (
            $syncPolicy->isReviseUpdateQtyMaxAppliedValueModeOn()
            && $productQty > $maxAppliedValue
            && $channelQty > $maxAppliedValue
        ) {
            return false;
        }

        if ($productQty === $channelQty) {
            return false;
        }

        return true;
    }

    private function updateConfiguratorAddPrice(
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator,
        \M2E\OnBuy\Model\Product $product
    ): void {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (
            $syncPolicy->isReviseUpdatePrice()
            && $this->isChangedPrice($product)
        ) {
            $configurator->allowPrice();
        }
    }

    private function isChangedPrice(\M2E\OnBuy\Model\Product $product): bool
    {
        return $product->getOnlinePrice() !== $product->getDataProvider()->getPrice()->getValue()->price;
    }

    private function updateConfiguratorAddShipping(
        Action\Configurator $configurator,
        \M2E\OnBuy\Model\Product $product
    ): void {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (
            $syncPolicy->isReviseUpdateShipping()
            && $this->isChangedShipping($product)
        ) {
            $configurator->allowShipping();
        }
    }

    private function isChangedShipping(
        \M2E\OnBuy\Model\Product $product
    ): bool {
        $deliveryTemplateId = $product->getDataProvider()->getDelivery()->getValue();
        $onlineDelivery = $product->getOnlineDeliveryTemplateId();

        return $deliveryTemplateId !== $onlineDelivery;
    }

    private function updateConfiguratorAddDetails(
        Action\Configurator $configurator,
        \M2E\OnBuy\Model\Product $product
    ): void {
        $syncPolicy = $product->getSynchronizationTemplate();

        $shouldReviseDetails = (
            $syncPolicy->isReviseUpdateTitle()
            || $syncPolicy->isReviseUpdateDescription()
            || $syncPolicy->isReviseUpdateImages()
            || $syncPolicy->isReviseUpdateCategories()
        );

        if ($shouldReviseDetails && $this->isChangedDetails($product)) {
            $configurator->allowDetails();
        }
    }

    private function isChangedDetails(
        \M2E\OnBuy\Model\Product $product
    ): bool {
        $title = $product->getDataProvider()->getTitle()->getValue();
        $onlineTitle = $product->getOnlineTitle();

        if ($title !== $onlineTitle) {
            return true;
        }

        $onlineDescription = $product->getOnlineDescription();
        $description = $product->getDataProvider()->getDescription()->getValue()->hash;

        if ($description !== $onlineDescription) {
            return true;
        }

        $onlineMainImage = $product->getOnlineMainImage();
        $mainImage = $product->getDataProvider()->getImages()->getValue()->mainImage;

        if ($mainImage !== $onlineMainImage) {
            return true;
        }

        $onlineImages = $product->getOnlineAdditionalImages();
        $images = $product->getDataProvider()->getImages()->getValue()->hashGalleryImages;

        if ($images !== $onlineImages) {
            return true;
        }

        $onlineCategoryId = $product->getOnlineCategoryId();
        $categoryId = $product->getDataProvider()->getCategoryData()->getValue();

        if ($onlineCategoryId !== $categoryId) {
            return true;
        }

        $onlineCategoryAttributes = $product->getOnlineCategoryAttributesData();
        $categoryAttributes = $product->getDataProvider()->getProductAttributesData()->getValue()->hash;

        if ($onlineCategoryAttributes !== $categoryAttributes) {
            return true;
        }

        return false;
    }

    public function calculateToRelist(\M2E\OnBuy\Model\Product $product, int $changer): Action
    {
        if (!$product->isRelistable()) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedRelistProduct($product, $changer)) {
            return Action::createNothing($product);
        }

        $configurator = new \M2E\OnBuy\Model\Product\Action\Configurator();
        $configurator->enableAll();

        return Action::createRelist($product, $configurator);
    }

    private function isNeedRelistProduct(\M2E\OnBuy\Model\Product $product, int $changer): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isRelistMode()) {
            return false;
        }

        if (
            $product->isStatusInactive()
            && $syncPolicy->isRelistFilterUserLock()
            && $product->isStatusChangerUser()
            && $changer !== \M2E\OnBuy\Model\Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistWhenQtyCalculatedHasValue()
            && !$this->isProductHasCalculatedQtyForListRevise($product, (int)$syncPolicy->getListWhenQtyCalculatedHasValue())
        ) {
            return false;
        }

        if (
            $syncPolicy->isReviseUpdatePrice()
            && $this->isChangedPrice($product)
        ) {
            return true;
        }

        if (
            $syncPolicy->isRelistAdvancedRulesEnabled()
            && !$this->isRelistAdvancedRuleMet($product, $syncPolicy)
        ) {
            return false;
        }

        return true;
    }

    private function isRelistAdvancedRuleMet(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Policy\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create()
            ->setData(
                [
                    'store_id' => $product->getListing()->getStoreId(),
                    'prefix' => \M2E\OnBuy\Model\Policy\Synchronization::RELIST_ADVANCED_RULES_PREFIX,
                ],
            );
        $ruleModel->loadFromSerialized($syncPolicy->getRelistAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }
}
