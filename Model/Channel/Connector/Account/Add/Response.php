<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Account\Add;

class Response
{
    private string $hash;

    private \M2E\OnBuy\Model\Channel\Account $account;

    public function __construct(
        string $hash,
        \M2E\OnBuy\Model\Channel\Account $account
    ) {
        $this->hash = $hash;
        $this->account = $account;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getAccount(): \M2E\OnBuy\Model\Channel\Account
    {
        return $this->account;
    }
}
