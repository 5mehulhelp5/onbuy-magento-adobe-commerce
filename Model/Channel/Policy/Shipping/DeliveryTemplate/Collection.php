<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate;

use M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate;

class Collection
{
    /** @var \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate[] */
    private array $deliveryTemplates = [];

    public function add(\M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate $deliveryTemplate): self
    {
        $this->deliveryTemplates[$deliveryTemplate->id] = $deliveryTemplate;

        return $this;
    }

    public function has(?string $id): bool
    {
        return isset($this->deliveryTemplates[$id]);
    }

    public function get(string $id): \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate
    {
        return $this->deliveryTemplates[$id];
    }

    public function isEmpty(): bool
    {
        return empty($this->deliveryTemplates);
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate[]
     */
    public function getAll(): array
    {
        return array_values($this->deliveryTemplates);
    }

    // ----------------------------------------

    public static function createFromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $deliveryTemplate) {
            $obj->add(DeliveryTemplate::createFromArray($deliveryTemplate));
        }

        return $obj;
    }

    public function toArray(): array
    {
        $deliveryTemplates = [];
        foreach ($this->deliveryTemplates as $deliveryTemplate) {
            $deliveryTemplates[] = $deliveryTemplate->toArray();
        }

        return $deliveryTemplates;
    }
}
