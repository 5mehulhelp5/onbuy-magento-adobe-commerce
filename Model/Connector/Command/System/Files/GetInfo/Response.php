<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Connector\Command\System\Files\GetInfo;

class Response
{
    private array $filesOptions;

    public function __construct(array $filesOptions)
    {
        $this->filesOptions = $filesOptions;
    }

    public function getFilesOptions(): array
    {
        return $this->filesOptions;
    }
}
