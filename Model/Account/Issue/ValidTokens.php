<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Account\Issue;

use M2E\OnBuy\Model\Issue\DataObject as Issue;

class ValidTokens implements \M2E\OnBuy\Model\Issue\LocatorInterface
{
    public const ACCOUNT_TOKENS_CACHE_KEY = 'onbuy_account_tokens_validations';

    private \M2E\OnBuy\Helper\View\OnBuy $viewHelper;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\OnBuy\Model\Issue\DataObjectFactory $issueFactory;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Channel\Connector\Account\GetAuthInfo\Processor $getAuthInfoProcessor;

    public function __construct(
        \M2E\OnBuy\Helper\View\OnBuy $viewHelper,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\OnBuy\Model\Issue\DataObjectFactory $issueFactory,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Channel\Connector\Account\GetAuthInfo\Processor $getAuthInfoProcessor
    ) {
        $this->viewHelper = $viewHelper;
        $this->cache = $cache;
        $this->issueFactory = $issueFactory;
        $this->accountRepository = $accountRepository;
        $this->getAuthInfoProcessor = $getAuthInfoProcessor;
    }

    /**
     * @inheritDoc
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \Exception
     */
    public function getIssues(): array
    {
        if (!$this->isNeedProcess()) {
            return [];
        }

        $accounts = $this->cache->getValue(self::ACCOUNT_TOKENS_CACHE_KEY);
        if ($accounts !== null) {
            return $this->prepareIssues($accounts);
        }

        try {
            $accounts = $this->retrieveNotValidAccounts();
        } catch (\Throwable $e) {
            $accounts = [];
        }

        $this->cache->setValue(
            self::ACCOUNT_TOKENS_CACHE_KEY,
            $accounts,
            ['account'],
            3600,
        );

        return $this->prepareIssues($accounts);
    }

    private function isNeedProcess(): bool
    {
        return $this->viewHelper->isInstallationWizardFinished();
    }

    /**
     * @return array
     * @throws \M2E\OnBuy\Model\Exception
     */
    private function retrieveNotValidAccounts(): array
    {
        $accounts = $this->accountRepository->getAll();
        if (empty($accounts)) {
            return [];
        }

        $authInfoCollection = $this->getAuthInfoProcessor->get($accounts);

        $result = [];
        foreach ($accounts as $account) {
            if (!$authInfoCollection->isValid($account->getServerHash())) {
                $result[] = [
                    'account_name' => $account->getTitle(),
                ];
            }
        }

        return $result;
    }

    private function prepareIssues(array $data): array
    {
        $issues = [];
        foreach ($data as $account) {
            $issues[] = $this->getIssue($account['account_name']);
        }

        return $issues;
    }

    private function getIssue(string $accountName): Issue
    {
        $text = __(
            "The token of %channel_title account \"%account_name\" is no longer valid.
         Please edit your %channel_title account and get a new token.",
            [
                'account_name' => $accountName,
                'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
            ],
        );

        return $this->issueFactory->createErrorDataObject($accountName, (string)$text, null);
    }
}
