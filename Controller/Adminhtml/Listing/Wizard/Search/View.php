<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Search;

use M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\OnBuy\Model\Settings $settings
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);
        $this->settings = $settings;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SEARCH_PRODUCTS_CHANNEL_ID;
    }

    protected function process(\M2E\OnBuy\Model\Listing $listing)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()
                         ->createBlock(
                             \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep\Grid::class,
                             '',
                             [ 'listing' => $this->uiListingRuntimeStorage->getListing(),
                               'wizardManager' => $this->uiWizardRuntimeStorage->getManager()],
                         );
            $this->setAjaxContent($grid);

            return;
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('OnBuy Product Search'));

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep::class,
            ),
        );

        return $this->getResult();
    }
}
