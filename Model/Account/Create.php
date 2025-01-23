<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Account;

class Create
{
    private \M2E\OnBuy\Model\Channel\Connector\Account\Add\Processor $addProcessor;
    private Repository $accountRepository;
    private \M2E\OnBuy\Model\AccountFactory $accountFactory;
    private \M2E\Core\Helper\Magento\Store $storeHelper;
    private \M2E\OnBuy\Model\Site\UpdateService $siteUpdateService;

    public function __construct(
        \M2E\OnBuy\Model\Site\UpdateService $siteUpdateService,
        \M2E\OnBuy\Model\AccountFactory $accountFactory,
        \M2E\OnBuy\Model\Channel\Connector\Account\Add\Processor $addProcessor,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\Core\Helper\Magento\Store $storeHelper
    ) {
        $this->siteUpdateService = $siteUpdateService;
        $this->addProcessor = $addProcessor;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        $this->storeHelper = $storeHelper;
    }

    /**
     * @param string $title
     * @param int $sellerId
     * @param string $consumerKey
     * @param string $secretKey
     *
     * @return \M2E\OnBuy\Model\Account
     * @throws \M2E\Core\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \M2E\OnBuy\Model\Exception\UnableAccountCreate
     */
    public function create(
        string $title,
        int $sellerId,
        string $consumerKey,
        string $secretKey
    ): \M2E\OnBuy\Model\Account {
        $response = $this->createOnServer(
            $title,
            $sellerId,
            $consumerKey,
            $secretKey
        );

        $channelAccount = $response->getAccount();
        $existAccount = $this->findExistAccountByIdentifier($channelAccount->identifier);
        if ($existAccount !== null) {
            throw new \M2E\OnBuy\Model\Exception(
                'An account with the same details has already been added. Please make sure you provide unique information.',
            );
        }

        $account = $this->accountFactory->create(
            $title,
            $channelAccount->identifier,
            $response->getHash(),
            $channelAccount->isTest,
            new \M2E\OnBuy\Model\Account\Settings\UnmanagedListings(),
            (new \M2E\OnBuy\Model\Account\Settings\Order())
                ->createWith(
                    ['listing_other' => ['store_id' => $this->storeHelper->getDefaultStoreId()]],
                ),
            new \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment(),
        );

        $this->accountRepository->create($account);
        $this->siteUpdateService->process($account, $channelAccount->sitesCollection);

        return $account;
    }

    // ----------------------------------------

    /**
     * @param string $title
     * @param int $sellerId
     * @param string $consumerKey
     * @param string $secretKey
     *
     * @return \M2E\OnBuy\Model\Channel\Connector\Account\Add\Response
     * @throws \M2E\Core\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\OnBuy\Model\Exception\UnableAccountCreate
     */
    private function createOnServer(
        string $title,
        int $sellerId,
        string $consumerKey,
        string $secretKey
    ): \M2E\OnBuy\Model\Channel\Connector\Account\Add\Response {
        return $this->addProcessor->process(
            $title,
            $sellerId,
            $consumerKey,
            $secretKey
        );
    }

    private function findExistAccountByIdentifier(string $identifier): ?\M2E\OnBuy\Model\Account
    {
        return $this->accountRepository->findByIdentifier($identifier);
    }
}
