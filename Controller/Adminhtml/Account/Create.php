<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Account;

class Create extends \M2E\OnBuy\Controller\Adminhtml\AbstractAccount
{
    private \M2E\OnBuy\Model\Account\Create $accountCreate;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Create $accountCreate,
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper
    ) {
        parent::__construct();

        $this->accountCreate = $accountCreate;
        $this->accountUrlHelper = $accountUrlHelper;
    }

    public function execute()
    {
        $consumerKey = $this->getRequest()->getPost('consumer_key');
        $secretKey = $this->getRequest()->getPost('secret_key');
        $title = $this->getRequest()->getPost('title');
        $sellerId = (int)$this->getRequest()->getPost('seller_id');

        $withoutEdit = (bool)$this->getRequest()->getParam('without_edit', false);
        if (
            empty($consumerKey)
            || empty($secretKey)
            || empty($title)
            || empty($sellerId)
        ) {
            $this->messageManager->addErrorMessage(
                __('Please complete all required fields before saving the configurations.')
            );
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->_redirect->getRefererUrl(),
                ]
            );

            return $this->getResult();
        }

        try {
            $account = $this->accountCreate->create($title, $sellerId, $consumerKey, $secretKey);
        } catch (\Throwable $e) {
            $message = (string)__(
                'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                [
                    'error_message' => $e->getMessage(),
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ],
            );

            $this->messageManager->addError($message);
            $this->setJsonContent(
                [
                    'result' => false,
                    'redirectUrl' => $this->_redirect->getRefererUrl(),
                ]
            );

            return $this->getResult();
        }

        $this->messageManager->addSuccessMessage(__('Account was created'));
        $this->setJsonContent(
            [
                'result' => true,
                'redirectUrl' => $withoutEdit
                    ? $this->_redirect->getRefererUrl()
                    : $this->accountUrlHelper->getEditUrl((int)$account->getId()),
            ]
        );

        return $this->getResult();
    }
}
