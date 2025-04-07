<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class ModeView extends \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    protected function getStepNick(): string
    {
        return \M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE;
    }

    protected function process(\M2E\OnBuy\Model\Listing $listing)
    {
        if ($this->isNeedSkipStep()) {
            $this->getWizardManager()
                 ->completeStep(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE, true);

            return $this->redirectToIndex($this->getWizardManager()->getWizardId());
        }

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\CategorySelectMode::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Your Categories'));

        $this->setPageHelpLink('https://docs-m2.m2epro.com/docs/set-categories-for-onbuy-products/');

        return $this->getResult();
    }

    private function isNeedSkipStep(): bool
    {
        return !$this->getWizardManager()->isEnabledCreateNewProductMode();
    }
}
