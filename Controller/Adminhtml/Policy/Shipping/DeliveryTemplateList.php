<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Policy\Shipping;

class DeliveryTemplateList extends \M2E\OnBuy\Controller\Adminhtml\AbstractTemplate
{
    private \M2E\OnBuy\Model\Policy\Shipping\ShippingService $shippingService;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Policy\Shipping\ShippingService $shippingService,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Model\Policy\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->siteRepository = $siteRepository;
        $this->accountRepository = $accountRepository;
        $this->shippingService = $shippingService;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $siteId = (int)$this->getRequest()->getParam('site_id');
        $account = $this->accountRepository->find($accountId);
        $site = $this->siteRepository->find($siteId);

        if ($account === null) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Account Id is required',
            ]);

            return $this->getResult();
        }

        if ($site === null) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Site Id is required',
            ]);

            return $this->getResult();
        }

        $force = (bool)(int)$this->getRequest()->getParam('force', 0);

        $deliveryTemplates = [];
        foreach ($this->shippingService->getAllDeliveryTemplates($account, $site, $force)->getAll() as $template) {
            $deliveryTemplates[] = [
                'id' => $template->id,
                'title' => $template->name,
            ];
        }

        $this->setJsonContent([
            'result' => true,
            'templates' => $deliveryTemplates,
        ]);

        return $this->getResult();
    }
}
