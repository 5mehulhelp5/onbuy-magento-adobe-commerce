<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Review;

class Complete extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\OnBuy\Helper\Data\Session $sessionHelper;
    private \M2E\OnBuy\Model\Listing\Wizard\CompleteProcessor $completeProcessor;
    private \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardRepository;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\Listing\Wizard\CompleteProcessor $completeProcessor,
        \M2E\OnBuy\Helper\Data\Session $sessionHelper,
        \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->sessionHelper = $sessionHelper;
        $this->completeProcessor = $completeProcessor;
        $this->wizardRepository = $wizardRepository;
    }

    public function execute()
    {
        $backUrl = $this->getRequest()->getParam('next_url');
        if (empty($backUrl) || !($backUrl = base64_decode($backUrl))) {
            return $this->redirectToIndex($this->getWizardIdFromRequest());
        }

        $id = $this->getWizardIdFromRequest();
        $wizardManager = $this->wizardManagerFactory->createById($id);

        if (!$wizardManager->isEnabledCreateNewProductMode()) {
            if (!empty($this->wizardRepository->getNotValidWizardProductsIds($wizardManager->getWizardId()))) {
                $this->getMessageManager()->addWarningMessage(
                    __(
                        'Magento products that could not be matched with items on the %channel_title marketplace were not added to the Listing',
                        [
                            'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                        ]
                    )
                );
            }
        }

        $listingProducts = $this->completeProcessor->process($wizardManager);

        $wizardManager->completeStep(
            \M2E\OnBuy\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_REVIEW,
        );
        $wizardManager->setProductCountTotal(count($listingProducts));

        if ($this->getRequest()->getParam('do_list')) {
            // temporary
            $ids = array_map(static function ($product) {
                return $product->getId();
            }, $listingProducts);
            $this->sessionHelper->setValue('added_products_ids', $ids);
        }

        return $this->_redirect($backUrl);
    }
}
