<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Order\UploadByUser;

class Configure extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
{
    private \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $managerFactory;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $managerFactory,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->managerFactory = $managerFactory;
        $this->accountRepository = $accountRepository;
        $this->siteRepository = $siteRepository;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        if (!isset($accountId)) {
            return $this->getErrorJsonResponse((string)__('Account id not set.'));
        }

        $from = $this->getRequest()->getParam('from_date');
        if (!isset($from)) {
            return $this->getErrorJsonResponse((string)__('From date not set.'));
        }

        $siteId = $this->getRequest()->getParam('site_id');
        if (!isset($siteId)) {
            return $this->getErrorJsonResponse((string)__('Site id not set.'));
        }

        // ---------------------------------------

        $account = $this->accountRepository->find((int)$accountId);
        if (!isset($account)) {
            return $this->getErrorJsonResponse((string)__('Not found Account.'));
        }

        $site = $this->siteRepository->find((int)$siteId);
        if ($site === null) {
            return $this->getErrorJsonResponse((string)__('Not found Site.'));
        }

        $manager = $this->managerFactory->create($account, $site);
        $fromDate = \M2E\Core\Helper\Date::timezoneDateToGmt($from);
        $toDate = \M2E\Core\Helper\Date::createCurrentGmt();

        try {
            $manager->setFromToDates($fromDate, $toDate);
        } catch (\Throwable $exception) {
            return $this->getErrorJsonResponse($exception->getMessage());
        }

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }

    // ---------------------------------------

    private function getErrorJsonResponse(string $errorMessage)
    {
        $json = [
            'result' => false,
            'messages' => [
                [
                    'type' => 'error',
                    'text' => $errorMessage,
                ],
            ],
        ];
        $this->setJsonContent($json);

        return $this->getResult();
    }
}
