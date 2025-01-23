<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing\Ui;

class RuntimeStorage
{
    private \M2E\OnBuy\Model\Listing $listing;

    public function hasListing(): bool
    {
        return isset($this->listing);
    }

    public function setListing(\M2E\OnBuy\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\OnBuy\Model\Listing
    {
        if (!$this->hasListing()) {
            throw new \LogicException('Listing was not initialized.');
        }

        return $this->listing;
    }
}
