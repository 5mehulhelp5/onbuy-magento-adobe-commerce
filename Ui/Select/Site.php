<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Select;

use Magento\Framework\Data\OptionSourceInterface;
use M2E\OnBuy\Model\Site\Repository;

class Site implements OptionSourceInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAllGroupBySiteId() as $site) {
            $options[] = [
                'label' => $site->getName(),
                'value' => $site->getSiteId(),
            ];
        }

        return $options;
    }
}
