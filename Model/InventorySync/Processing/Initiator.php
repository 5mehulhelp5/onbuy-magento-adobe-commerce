<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync\Processing;

class Initiator implements \M2E\OnBuy\Model\Processing\PartialInitiatorInterface
{
    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Site $site;

    public function __construct(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ) {
        $this->account = $account;
        $this->site = $site;
    }

    public function getInitCommand(): \M2E\OnBuy\Model\Channel\Connector\Inventory\InventoryGetItemsCommand
    {
        return new \M2E\OnBuy\Model\Channel\Connector\Inventory\InventoryGetItemsCommand(
            $this->account->getServerHash(),
            $this->site->getSiteId()
        );
    }

    public function generateProcessParams(): array
    {
        return [
            'account_id' => $this->account->getId(),
            'site_id' => $this->site->getId(),
            'current_date' => \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        ];
    }

    public function getResultHandlerNick(): string
    {
        return ResultHandler::NICK;
    }

    public function initLock(\M2E\OnBuy\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->create(\M2E\OnBuy\Model\Site::LOCK_NICK, $this->site->getId());
    }
}
