<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Order\UploadByUser;

class Reset extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
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
            return $this->getErrorJsonResponse((string)__('Account must be specified.'));
        }

        $account = $this->accountRepository->find((int)$accountId);
        if ($account === null) {
            return $this->getErrorJsonResponse((string)__('Not found Account.'));
        }

        $manager = $this->managerFactory->create($account);
        $manager->clear();

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
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
