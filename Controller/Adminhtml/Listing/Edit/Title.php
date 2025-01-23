<?php

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Edit;

use M2E\OnBuy\Controller\Adminhtml\AbstractListing;

class Title extends AbstractListing
{
    /** @var \M2E\OnBuy\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Helper\Data\GlobalData $globalData,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listing = $this->listingRepository->get($params['id']);

        if ($this->getRequest()->isPost()) {
            $listing->addData($params);
            $this->listingRepository->save($listing);

            return $this->getResult();
        }

        $this->globalData->setValue('edit_listing', $listing);

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Edit\Title::class
            )
        );

        return $this->getResult();
    }
}
