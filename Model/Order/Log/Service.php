<?php

namespace M2E\OnBuy\Model\Order\Log;

class Service
{
    private int $initiator = \M2E\Core\Helper\Data::INITIATOR_EXTENSION;

    private \M2E\OnBuy\Model\Order\LogFactory $orderLogFactory;
    private \M2E\OnBuy\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollection;
    private \M2E\OnBuy\Model\Order\Log\Repository $orderLogRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\LogFactory $orderLogFactory,
        \M2E\OnBuy\Model\Order\Log\Repository $orderLogRepository,
        \M2E\OnBuy\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollection
    ) {
        $this->orderLogFactory = $orderLogFactory;
        $this->orderLogCollection = $orderLogCollection;
        $this->orderLogRepository = $orderLogRepository;
    }

    public function setInitiator(int $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function getInitiator(): ?int
    {
        return $this->initiator;
    }

    /**
     * @param \M2E\OnBuy\Model\Order $order
     * @param string $description
     * @param int $type
     * @param array $additionalData
     * @param bool $isUnique
     *
     * @return bool
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addMessage(
        \M2E\OnBuy\Model\Order $order,
        string $description,
        int $type,
        array $additionalData = [],
        bool $isUnique = false
    ): bool {
        if (empty($order->getId())) {
            return false;
        }

        if (
            $isUnique
            && $this->isExist($order->getId(), $description)
        ) {
            return false;
        }

        $orderLog = $this->orderLogFactory->create();
        $orderLog->setAccountId($order->getAccountId());
        $orderLog->setOrderId($order->getId());
        $orderLog->setDescription($description);
        $orderLog->setType($type);
        $orderLog->setInitiator($this->getInitiator());
        $orderLog->setAdditionalData(\M2E\Core\Helper\Json::encode($additionalData));
        $orderLog->setCreateDate(\M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'));

        $this->orderLogRepository->save($orderLog);

        return true;
    }

    private function isExist(int $orderId, string $message): bool
    {
        $collection = $this->orderLogCollection->create();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->addFieldToFilter('description', $message);

        return ($collection->getSize() > 0);
    }
}
