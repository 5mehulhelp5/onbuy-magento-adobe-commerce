<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\ProductSource;

class View extends \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    protected function getStepNick(): string
    {
        return \M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCT_SOURCE;
    }

    protected function process(\M2E\OnBuy\Model\Listing $listing)
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Add Magento Products'));

        $this->setPageHelpLink('https://docs-m2.m2epro.com/docs/create-m2e-onbuy-listing/');

        return $this->getResult();
    }
}
