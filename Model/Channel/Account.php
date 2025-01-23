<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel;

class Account
{
    public string $identifier;
    public bool $isTest;
    /** @var Site[] */
    public array $sites;
    /** @var \M2E\OnBuy\Model\Channel\SiteCollection */
    public SiteCollection $sitesCollection;

    public function __construct(
        string $identifier,
        bool $isTest,
        SiteCollection $sitesCollection
    ) {
        $this->identifier = $identifier;
        $this->isTest = $isTest;
        $this->sitesCollection = $sitesCollection;
    }
}
