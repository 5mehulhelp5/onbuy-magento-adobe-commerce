<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class NoteFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Note
    {
        return $this->objectManager->create(Note::class);
    }

    public function create(int $orderId, string $note): Note
    {
        $obj = $this->createEmpty();

        $obj->create($orderId, $note);

        return $obj;
    }
}
