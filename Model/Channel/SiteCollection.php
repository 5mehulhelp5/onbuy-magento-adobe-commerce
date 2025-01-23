<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel;

class SiteCollection
{
    /** @var Site[] */
    private array $sites = [];

    public function add(Site $site): void
    {
        $this->sites[$site->id] = $site;
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Site[]
     */
    public function getAll(): array
    {
        return array_values($this->sites);
    }
}
