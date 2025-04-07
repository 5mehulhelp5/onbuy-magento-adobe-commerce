<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Attribute;

class Item
{
    public const PRODUCT_TYPE = 'PRODUCT_PROPERTY';

    private string $id;
    private string $name;
    private string $type;
    private bool $isRequired;
    private array $values = [];

    public function __construct(
        string $id,
        string $name,
        string $type,
        bool $isRequired
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->isRequired = $isRequired;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isProductType(): bool
    {
        return $this->type === self::PRODUCT_TYPE;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @return list<array{id:string, name:string}>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function addValue(string $id, string $name): void
    {
        $this->values[] = [
            'id' => $id,
            'name' => $name
        ];
    }
}
