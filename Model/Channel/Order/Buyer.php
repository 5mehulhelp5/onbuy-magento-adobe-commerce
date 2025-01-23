<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class Buyer
{
    public string $name;
    public string $email;
    public ?string $phone;

    public function __construct(
        string $name,
        string $email,
        ?string $phone
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
    }
}
