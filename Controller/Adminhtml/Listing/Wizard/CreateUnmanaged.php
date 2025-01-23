<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard;

class CreateUnmanaged extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    use \M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Listing\Wizard\Create $createModel;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $listingOtherRepository;
    private \M2E\OnBuy\Helper\Data\Session $sessionDataHelper;
    private \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Model\Listing\Wizard\Create $createModel,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $listingOtherRepository,
        \M2E\OnBuy\Helper\Data\Session $sessionDataHelper,
        \M2E\OnBuy\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->createModel = $createModel;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->productRepository = $productRepository;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $listingId = (int)$this->getRequest()->getParam('listingId');
        if (empty($listingId)) {
            $this->getMessageManager()->addError(__('Cannot start Wizard, Listing ID must be provided first.'));

            return $this->_redirect('*/listing/index');
        }

        $listing = $this->listingRepository->get($listingId);
        $wizard = $this->createModel->process($listing, \M2E\OnBuy\Model\Listing\Wizard::TYPE_UNMANAGED);
        $manager = $this->wizardManagerFactory->create($wizard);

        $sessionKey = \M2E\OnBuy\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $selectedProducts = $this->sessionDataHelper->getValue($sessionKey);

        $errorsCount = 0;
        foreach ($selectedProducts as $otherListingId) {
            $unmanagedProduct = $this->listingOtherRepository->get((int)$otherListingId);

            if (!$unmanagedProduct->isListingCorrectForMove($listing)) {
                $errorsCount++;
                continue;
            }

            if ($this->productRepository->findByListingAndMagentoProductId($listing, $unmanagedProduct->getMagentoProductId())) {
                $errorsCount++;
                continue;
            }

            $wizardProduct = $manager->addUnmanagedProduct($unmanagedProduct);

            if ($wizardProduct === null) {
                $errorsCount++;
            }
        }

        $this->sessionDataHelper->removeValue($sessionKey);

        if ($errorsCount) {
            if (count($selectedProducts) == $errorsCount) {
                $manager->cancel();
                $this->getMessageManager()->addErrorMessage(
                    __(
                        'Some products were not moved because they already exist in the selected Listing or do
                         not belong to the channel account or site of the listing'
                    )
                );

                return $this->_redirect($this->urlHelper->getGridUrl(['account' => $listing->getAccountId()]));
            }

            $this->getMessageManager()->addErrorMessage(
                __(
                    'Some products were not moved because they already exist in the selected Listing or do
                     not belong to the channel account or site of the listing'
                )
            );
        }

        return $this->_redirect('*/listing_wizard/index', ['id' => $wizard->getId()]);
    }
}
