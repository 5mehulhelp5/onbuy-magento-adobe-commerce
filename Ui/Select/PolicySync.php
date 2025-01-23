<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Select;

class PolicySync implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\OnBuy\Model\Policy\Synchronization\Repository $repository;

    public function __construct(\M2E\OnBuy\Model\Policy\Synchronization\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getAll() as $policy) {
            $options[] = [
                'label' => $policy->getTitle(),
                'value' => $policy->getId(),
            ];
        }

        return $options;
    }
}
