<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary;

use M2E\OnBuy\Model\Category\Dictionary;

class Manager
{
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\OnBuy\Model\Category\Dictionary\CreateService $createService;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\OnBuy\Model\Category\Dictionary\CreateService $createService,
        \M2E\OnBuy\Model\Account\Repository $accountRepository
    ) {
        $this->dictionaryRepository = $dictionaryRepository;
        $this->createService = $createService;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getOrCreateDictionary(int $accountId, int $siteId, int $categoryId): Dictionary
    {
        $entity = $this->dictionaryRepository->findBySiteIdAndCategoryId($siteId, $categoryId);
        if ($entity !== null) {
            return $entity;
        }

        $account = $this->accountRepository->get($accountId);

        return $this->createService->create($account->getServerHash(), $siteId, $categoryId);
    }
}
