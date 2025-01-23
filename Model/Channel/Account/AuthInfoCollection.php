<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Account;

class AuthInfoCollection
{
    private array $accountsInfo = [];

    public function add(string $serverHash, bool $isValid): void
    {
        $this->accountsInfo[$serverHash] = $isValid;
    }

    public function isValid(string $serverHash): bool
    {
        return $this->accountsInfo[$serverHash] ?? true;
    }
}
