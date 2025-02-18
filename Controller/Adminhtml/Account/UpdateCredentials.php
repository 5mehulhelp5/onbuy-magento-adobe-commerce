<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Account;

class UpdateCredentials extends \M2E\OnBuy\Controller\Adminhtml\AbstractAccount
{
    private \M2E\OnBuy\Helper\Module\Exception $helperException;
    private \M2E\OnBuy\Model\Account\Update $accountUpdate;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Update $accountUpdate,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Helper\Module\Exception $helperException,
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper
    ) {
        parent::__construct();

        $this->helperException = $helperException;
        $this->accountUpdate = $accountUpdate;
        $this->accountRepository = $accountRepository;
        $this->accountUrlHelper = $accountUrlHelper;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('id', 0);

        if ($accountId === 0) {
            $this->messageManager->addErrorMessage(__('Account does not exist.'));
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->_redirect->getRefererUrl(),
                ]
            );

            return $this->getResult();
        }

        $account = $this->accountRepository->get($accountId);
        $consumerKey = $this->getRequest()->getPost('consumer_key');
        $secretKey = $this->getRequest()->getPost('secret_key');

        $resultUrl = $this->accountUrlHelper->getEditUrl($accountId);
        if (
            empty($consumerKey)
            || empty($secretKey)
        ) {
            $this->messageManager->addErrorMessage(
                __('Please complete all required fields before saving the configurations.')
            );
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $resultUrl,
                ]
            );

            return $this->getResult();
        }

        try {
            $this->accountUpdate->updateCredentials(
                $account,
                $consumerKey,
                $secretKey,
            );
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);

            $message = __(
                'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                [
                    'error_message' => $exception->getMessage(),
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ],
            );

            $this->messageManager->addError($message);
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $resultUrl,
                ]
            );

            return $this->getResult();
        }

        $this->messageManager->addSuccessMessage(__('Access Details were updated'));
        $this->setJsonContent(
            [
                'result' => true,
                'redirectUrl' => $resultUrl,
            ]
        );

        return $this->getResult();
    }
}
