<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Select;

class Errors implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\OnBuy\Model\Tag\Repository $repository;

    public function __construct(\M2E\OnBuy\Model\Tag\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAllTags() as $tag) {
            if ($tag->getErrorCode() === \M2E\OnBuy\Model\Tag::HAS_ERROR_ERROR_CODE) {
                continue;
            }

            $options[] = [
                'label' => substr($tag->getText(), 0, 40) . '...',
                'value' => $tag->getErrorCode(),
            ];
        }

        return $options;
    }
}
