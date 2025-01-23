<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Note;

use M2E\OnBuy\Model\ResourceModel\Order\Note as NoteResource;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Order\Note $noteResource;
    private \M2E\OnBuy\Model\Order\NoteFactory $noteFactory;
    private \M2E\OnBuy\Model\ResourceModel\Order\Note\CollectionFactory $noteCollectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Order\Note $noteResource,
        \M2E\OnBuy\Model\Order\NoteFactory $noteFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\Note\CollectionFactory $noteCollectionFactory
    ) {
        $this->noteResource = $noteResource;
        $this->noteFactory = $noteFactory;
        $this->noteCollectionFactory = $noteCollectionFactory;
    }

    public function create(\M2E\OnBuy\Model\Order\Note $note): void
    {
        $this->noteResource->save($note);
    }

    public function save(\M2E\OnBuy\Model\Order\Note $note): void
    {
        $this->noteResource->save($note);
    }

    public function remove(\M2E\OnBuy\Model\Order\Note  $note): void
    {
        $this->noteResource->delete($note);
    }

    public function get(int $id): \M2E\OnBuy\Model\Order\Note
    {
        $note = $this->find($id);
        if ($note === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic("Order Note $id not found.");
        }

        return $note;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Order\Note
    {
        $note = $this->noteFactory->createEmpty();
        $this->noteResource->load($note, $id);

        if ($note->isObjectNew()) {
            return null;
        }

        return $note;
    }

    public function getOrderNoteCollectionByOrderId(int $orderId): \M2E\OnBuy\Model\ResourceModel\Order\Note\Collection
    {
        $collection = $this->noteCollectionFactory->create();
        $collection->addFieldToFilter(NoteResource::COLUMN_ORDER_ID, $orderId);

        return $collection;
    }

    public function getOrderNoteCollectionByOrderIds(array $orderIds): \M2E\OnBuy\Model\ResourceModel\Order\Note\Collection
    {
        $collection = $this->noteCollectionFactory->create();
        $collection->addFieldToFilter(NoteResource::COLUMN_ORDER_ID, ['in' => $orderIds]);

        return $collection;
    }
}
