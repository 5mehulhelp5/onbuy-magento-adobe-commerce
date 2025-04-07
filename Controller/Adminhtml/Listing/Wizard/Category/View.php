<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\OnBuy\Block\Adminhtml\Listing\Wizard\CategorySelectMode;
use M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);
        $this->dictionaryRepository = $dictionaryRepository;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY;
    }

    protected function process(\M2E\OnBuy\Model\Listing $listing)
    {
        if ($this->isNeedSkipStep()) {
            $this->getWizardManager()
                 ->completeStep(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY, true);

            return $this->redirectToIndex($this->getWizardManager()->getWizardId());
        }

        $manager = $this->getWizardManager();
        $selectedMode = $manager->getStepData(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE);

        $mode = $selectedMode['mode'];

        if ($mode === CategorySelectMode::MODE_SAME) {
            return $this->stepSelectCategoryModeSame();
        }

        if ($mode === CategorySelectMode::MODE_MANUALLY) {
            return $this->stepSelectCategoryModeManually();
        }

        throw new \LogicException('Category mode unknown.');
    }

    private function isNeedSkipStep(): bool
    {
        return !$this->getWizardManager()->isEnabledCreateNewProductMode();
    }

    private function stepSelectCategoryModeSame()
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Category\Same::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Category (All Products same Category)'));

        return $this->getResult();
    }

    private function stepSelectCategoryModeManually()
    {
        $manager = $this->getWizardManager();
        $wizardProducts = $manager->getNotProcessedProducts();
        $categoriesData = $this->getCategoriesData($wizardProducts);

        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Category\Manually::class,
                '',
                [
                    'categoriesData' => $categoriesData,
                ]
            );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setAjaxContent($block->getChildBlock('grid')->toHtml());

            return $this->getResult();
        }

        $this->addContent($block);

        $this->getResultPage()->getConfig()->getTitle()->prepend(
            __('Set Category (Manually for each Product)')
        );

        return $this->getResult();
    }

    /**
     * @param \M2E\OnBuy\Model\Listing\Wizard\Product[] $wizardProduct
     *
     * @return array
     */
    private function getCategoriesData(array $wizardProduct): array
    {
        $result = [];
        foreach ($wizardProduct as $product) {
            $productData = [
                'product_id' => $product->getId(),
            ];

            $categoryDictionary = $product->getCategoryDictionary();
            if ($categoryDictionary !== null) {
                $productData['value'] = $categoryDictionary->getCategoryId();
                $productData['path'] = $categoryDictionary->getPath();
            }

            $result[$product->getMagentoProductId()] = $productData;
        }

        return $result;
    }
}
