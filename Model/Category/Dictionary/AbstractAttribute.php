<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary;

abstract class AbstractAttribute
{
    protected string $id;
    private string $name;
    private bool $isRequired;
    /** @var \M2E\OnBuy\Model\Category\Dictionary\Attribute\Value[] */
    private array $recommendedValues;

    public function __construct(
        string $id,
        string $name,
        bool $isRequired,
        array $recommendedValues = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->recommendedValues = $recommendedValues;
    }

    abstract public function getType(): string;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isMultipleSelected(): bool
    {
        return false;
    }

    /**
     * @return array|\M2E\OnBuy\Model\Category\Dictionary\Attribute\Value[]
     */
    public function getValues(): array
    {
        return $this->recommendedValues;
    }
}
