<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class ProcessingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Processing
    {
        return $this->objectManager->create(Processing::class);
    }

    public function create(
        int $type,
        string $serverHash,
        string $handleNick,
        array $params,
        \DateTime $expireDate
    ): Processing {
        $obj = $this->createEmpty();

        $obj->create($type, $serverHash, $handleNick, $params, $expireDate);

        return $obj;
    }
}
