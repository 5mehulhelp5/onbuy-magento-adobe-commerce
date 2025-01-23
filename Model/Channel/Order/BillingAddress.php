<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class BillingAddress
{
    public ?string $name;
    public ?string $line1;
    public ?string $line2;
    public ?string $line3;
    public ?string $city;
    public ?string $county;
    public ?string $postCode;
    public ?string $country;
    public ?string $countryCode;

    public function __construct(
        ?string $name,
        ?string $line1,
        ?string $line2,
        ?string $line3,
        ?string $city,
        ?string $county,
        ?string $postCode,
        ?string $country,
        ?string $countryCode
    ) {
        $this->name = $name;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->line3 = $line3;
        $this->city = $city;
        $this->county = $county;
        $this->postCode = $postCode;
        $this->country = $country;
        $this->countryCode = $countryCode;
    }
}
