<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Account;

class SiteList extends \M2E\OnBuy\Controller\Adminhtml\AbstractAccount
{
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository
    ) {
        parent::__construct();

        $this->siteRepository = $siteRepository;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');

        if (empty($accountId)) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Account Id is required',
            ]);

            return $this->getResult();
        }

        $sites = $this->siteRepository->findForAccount((int)$accountId);
        $sites = array_map(static function (\M2E\OnBuy\Model\Site $entity) {
            return [
                'id' => $entity->getId(),
                'country_code' => $entity->getCountryCode(),
            ];
        }, $sites);

        $this->setJsonContent([
            'result' => true,
            'sites' => $sites,
        ]);

        return $this->getResult();
    }
}
