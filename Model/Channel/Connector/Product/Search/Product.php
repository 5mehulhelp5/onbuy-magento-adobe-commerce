<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Product\Search;

class Product
{
    private string $identifier;
    private string $opc;
    private string $name;
    private string $url;
    private string $img;

    public function __construct(
        string $identifier,
        string $opc,
        string $name,
        string $url,
        string $img
    ) {
        $this->identifier = $identifier;
        $this->opc = $opc;
        $this->name = $name;
        $this->url = $url;
        $this->img = $img;
    }

    public function getOpc(): string
    {
        return $this->opc;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImg(): string
    {
        return $this->img;
    }
}
