<?php

namespace M2E\OnBuy\Controller\Adminhtml\Account;

use M2E\OnBuy\Controller\Adminhtml\AbstractAccount;

class Edit extends AbstractAccount
{
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Connector\Client\Single $serverClient
    ) {
        parent::__construct();

        $this->serverClient = $serverClient;
        $this->accountRepository = $accountRepository;
    }

    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        $account = null;
        if ($id = $this->getRequest()->getParam('id')) {
            $account = $this->accountRepository->find((int)$id);
        }

        if ($account === null && $id) {
            $this->messageManager->addError(__('Account does not exist.'));

            return $this->_redirect('*/onbuy_account');
        }

        if ($account !== null) {
            $this->addLicenseMessage($account);
        }

        $headerTextEdit = __('Edit Account');
        $headerTextAdd = __('Add Account');

        if ($account !== null) {
            $headerText = $headerTextEdit;
            $headerText .= ' "' . \M2E\Core\Helper\Data::escapeHtml($account->getTitle()) . '"';
        } else {
            $headerText = $headerTextAdd;
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend($headerText);

        /** @var \M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs $tabsBlock */
        $tabsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs::class, '', [
                'account' => $account,
            ]);
        $this->addLeft($tabsBlock);

        /** @var \M2E\OnBuy\Block\Adminhtml\Account\Edit $contentBlock */
        $contentBlock = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Edit::class, '', [
                'account' => $account,
            ]);
        $this->addContent($contentBlock);

        return $this->getResultPage();
    }

    private function addLicenseMessage(\M2E\OnBuy\Model\Account $account): void
    {
        try {
            $command = new \M2E\OnBuy\Model\Connector\Command\Account\Get\InfoCommand(
                $account->getServerHash(),
            );
            /** @var \M2E\OnBuy\Model\Connector\Command\Account\Get\Status $status */
            $status = $this->serverClient->process($command);
        } catch (\Throwable $e) {
            return;
        }

        if ($status->isActive()) {
            return;
        }

        $this->addExtendedErrorMessage(
            __(
                'Work with this Account is currently unavailable for the following reason: <br/> %error_message',
                ['error_message' => $status->getNote()],
            ),
        );
    }
}
