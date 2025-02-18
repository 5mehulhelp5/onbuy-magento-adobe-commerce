<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Policy\Shipping;

class DeliveryTemplate
{
    public int $id;
    public string $name;
    public bool $isDefault;

    public function __construct(
        int $id,
        string $name,
        bool $isDefault
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isDefault = $isDefault;
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['is_default'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_default' => $this->isDefault
        ];
    }
}
