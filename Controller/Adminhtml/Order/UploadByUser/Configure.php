<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Order\UploadByUser;

class Configure extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
{
    private \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $managerFactory;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $managerFactory,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->managerFactory = $managerFactory;
        $this->accountRepository = $accountRepository;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        if (empty($accountId)) {
            $this->getErrorJsonResponse((string)__('Account id not set.'));
        }

        $account = $this->accountRepository->find((int)$accountId);
        if ($account === null) {
            return $this->getErrorJsonResponse((string)__('Not found Account.'));
        }

        $from = $this->getRequest()->getParam('from_date');
        if (empty($from)) {
            return $this->getErrorJsonResponse((string)__('From date not set.'));
        }

        $to = $this->getRequest()->getParam('to_date');
        if (empty($to)) {
            return $this->getErrorJsonResponse((string)__('To date not set.'));
        }

        $manager = $this->managerFactory->create($account);
        $fromDate = \M2E\Core\Helper\Date::timezoneDateToGmt($from);
        $toDate = \M2E\Core\Helper\Date::timezoneDateToGmt($to);

        if ($this->isMoreThanCurrentDate($toDate)) {
            $toDate = \M2E\Core\Helper\Date::createCurrentGmt();
        }

        try {
            $manager->setFromToDates($fromDate, $toDate);
        } catch (\Throwable $exception) {
            return $this->getErrorJsonResponse($exception->getMessage());
        }

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }

    // ---------------------------------------

    /**
     * @throws \Exception
     */
    protected function isMoreThanCurrentDate(\DateTime $toDate): bool
    {
        $nowTimestamp = \M2E\Core\Helper\Date::createCurrentGmt()->getTimestamp();

        if ($toDate->getTimestamp() > $nowTimestamp) {
            return true;
        }

        return false;
    }

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
