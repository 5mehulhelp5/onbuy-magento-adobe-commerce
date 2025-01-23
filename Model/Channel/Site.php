<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel;

class Site
{
    public int $id;
    public string $name;
    public string $countryCode;
    public string $currencyCode;

    public function __construct(
        int $id,
        string $name,
        string $countryCode,
        string $currencyCode
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->countryCode = $countryCode;
        $this->currencyCode = $currencyCode;
    }
}
