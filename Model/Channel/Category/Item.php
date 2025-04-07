<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Category;

class Item
{
    private int $id;
    private string $title;
    private bool $isLeaf;
    private ?int $parentId;

    public function __construct(
        int $id,
        string $title,
        bool $isLeaf,
        ?int $parentId
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->isLeaf = $isLeaf;
        $this->parentId = $parentId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isLeaf(): bool
    {
        return $this->isLeaf;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }
}
