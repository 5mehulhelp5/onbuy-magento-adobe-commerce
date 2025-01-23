<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

class CheckMessages extends \M2E\OnBuy\Controller\Adminhtml\AbstractBase
{
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingRepository;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingRepository,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->sellingRepository = $sellingRepository;
        $this->storeManager = $storeManager;
        $this->siteRepository = $siteRepository;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        $data = $this->getRequest()->getParam($nick);

        $template = null;
        $templateData = $data ?? [];

        if ($nick == \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT) {
            $template = $this->sellingRepository->find($id);
        }

        if ($template !== null && $template->getId()) {
            $templateData = $template->getData();
        }

        if ($template === null || empty($templateData)) {
            $this->setJsonContent(['messages' => '']);

            return $this->getResult();
        }

        $store = $this->storeManager->getStore((int)$this->getRequest()->getParam('store_id'));
        $site = $this->siteRepository->get((int)$this->getRequest()->getParam('site_id'));

        /** @var \M2E\OnBuy\Block\Adminhtml\Template\SellingFormat\Messages $messagesBlock */
        $messagesBlock = $this->getLayout()
                              ->createBlock(
                                  \M2E\OnBuy\Block\Adminhtml\Template\SellingFormat\Messages::class,
                                  '',
                                  [
                                      'store' => $store,
                                      'site'  => $site
                                  ]
                              );

        $this->setJsonContent(['messages' => $messagesBlock->getMessagesHtml()]);

        return $this->getResult();
    }
}
