<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

use M2E\OnBuy\Model\ResourceModel\Order as OrderResource;
use M2E\OnBuy\Model\ResourceModel\Site as SiteResource;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private OrderResource $orderResource;
    private \M2E\OnBuy\Model\OrderFactory $orderFactory;
    private \Magento\Sales\Model\ResourceModel\Order $magentoOrderResource;
    private \M2E\OnBuy\Model\ResourceModel\Site $siteResource;

    public function __construct(
        OrderResource $orderResource,
        \M2E\OnBuy\Model\OrderFactory $orderFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order $magentoOrderResource,
        \M2E\OnBuy\Model\ResourceModel\Site $siteResource
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->magentoOrderResource = $magentoOrderResource;
        $this->siteResource = $siteResource;
    }

    public function get(int $id): \M2E\OnBuy\Model\Order
    {
        $order = $this->find($id);
        if ($order === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic("Order $id not found.");
        }

        return $order;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Order
    {
        $order = $this->orderFactory->createEmpty();
        $this->orderResource->load($order, $id);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function findByMagentoOrderId(int $id): ?\M2E\OnBuy\Model\Order
    {
        $order = $this->orderFactory->createEmpty();
        $this->orderResource->load($order, $id, OrderResource::COLUMN_MAGENTO_ORDER_ID);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function getCollection(
        ?int $accountId,
        ?int $siteId,
        bool $notCreatedOnly = false
    ): \M2E\OnBuy\Model\ResourceModel\Order\Collection {
        $collection = $this->orderCollectionFactory->create();

        $collection
            ->getSelect()
            ->joinLeft(
                ['so' => $this->magentoOrderResource->getMainTable()],
                '(so.entity_id = `main_table`.magento_order_id)',
                ['magento_order_num' => 'increment_id']
            )
            ->joinLeft(
                ['site' => $this->siteResource->getMainTable()],
                'main_table.site_id = site.id',
                [
                    'site_' . SiteResource::COLUMN_ID => SiteResource::COLUMN_ID,
                    'site_' . SiteResource::COLUMN_SITE_ID => SiteResource::COLUMN_SITE_ID
                ]
            );

        if ($accountId !== null) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        if ($siteId !== null) {
            $collection->addFieldToFilter('site.site_id', $siteId);
        }

        if ($notCreatedOnly) {
            $collection->addFieldToFilter('magento_order_id', ['null' => true]);
        }

        return $collection;
    }

    public function removeByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getConnection()->delete(
            $orderCollection->getMainTable(),
            ['account_id = ?' => $accountId]
        );
    }

    public function removeRelatedOrderItemsByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderItemCollection = $this->orderItemCollectionFactory->create();
        $orderItemCollection->getConnection()->delete(
            $orderItemCollection->getMainTable(),
            [
                \M2E\OnBuy\Model\ResourceModel\Order\Item::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    public function removeRelatedOrderChangesByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->getConnection()->delete(
            $orderChangeCollection->getMainTable(),
            [
                \M2E\OnBuy\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    public function removeRelatedOrderNoteByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderNoteCollection = $this->orderNoteCollectionFactory->create();
        $orderNoteCollection->getConnection()->delete(
            $orderNoteCollection->getMainTable(),
            [
                \M2E\OnBuy\Model\ResourceModel\Order\Note::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    public function findByAccountAndSiteByChannelId(
        int $accountId,
        int $siteId,
        string $channelOrderId
    ): ?\M2E\OnBuy\Model\Order {
        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter(OrderResource::COLUMN_ACCOUNT_ID, ['eq' => $accountId]);
        $collection->addFieldToFilter(OrderResource::COLUMN_SITE_ID, ['eq' => $siteId]);
        $collection->addFieldToFilter(OrderResource::COLUMN_CHANNEL_ORDER_ID, ['eq' => $channelOrderId]);
        $collection->setOrder(OrderResource::COLUMN_ID);
        $collection->setPageSize(1);

        $order = $collection->getFirstItem();

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function create(\M2E\OnBuy\Model\Order $order): void
    {
        $this->orderResource->save($order);
    }

    public function save(\M2E\OnBuy\Model\Order $order): void
    {
        $this->orderResource->save($order);
    }

    /**
     * @param array $orderIds
     *
     * @return \M2E\OnBuy\Model\Order[]
     */
    public function findByIds(array $orderIds): array
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(OrderResource::COLUMN_ID, ['in' => $orderIds]);

        return array_values($collection->getItems());
    }

    /**
     * @param array $ids
     *
     * @return \M2E\OnBuy\Model\Order[]
     */
    public function findOrdersForReservationCancel(array $ids): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(\M2E\OnBuy\Model\ResourceModel\Order::COLUMN_ID, ['in' => $ids]);
        $orderCollection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_RESERVATION_STATE,
            \M2E\OnBuy\Model\Order\Reserve::STATE_PLACED
        );

        return array_values($orderCollection->getItems());
    }

    /**
     * @param array $ids
     *
     * @return \M2E\OnBuy\Model\Order[]
     */
    public function findOrdersForReservationPlace(array $ids): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(\M2E\OnBuy\Model\ResourceModel\Order::COLUMN_ID, ['in' => $ids]);
        $orderCollection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_RESERVATION_STATE,
            ['neq' => \M2E\OnBuy\Model\Order\Reserve::STATE_PLACED]
        );
        $orderCollection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_ID,
            ['null' => true]
        );

        return array_values($orderCollection->getItems());
    }

    /**
     * @param \M2E\OnBuy\Model\Account $account
     *
     * @return \M2E\OnBuy\Model\Order[]
     * @throws \Exception
     */
    public function findForReleaseReservation(\M2E\OnBuy\Model\Account $account): array
    {
        $collection = $this->orderCollectionFactory->create()
                                                   ->addFieldToFilter(
                                                       \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
                                                       $account->getId()
                                                   )
                                                   ->addFieldToFilter(
                                                       \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_RESERVATION_STATE,
                                                       \M2E\OnBuy\Model\Order\Reserve::STATE_PLACED
                                                   );

        $reservationDays = $account->getOrdersSettings()->getQtyReservationDays();

        $minReservationStartDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $minReservationStartDate->modify('- ' . $reservationDays . ' days');
        $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

        $collection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_RESERVATION_START_DATE,
            ['lteq' => $minReservationStartDate]
        );

        return $collection->getItems();
    }

    /**
     * @param \M2E\OnBuy\Model\Account $account
     * @param \DateTime $borderDate
     * @param int $creationAttemptsLessThan
     * @param int $limit
     *
     * @return \M2E\OnBuy\Model\Order[]
     */
    public function findForAttemptMagentoCreate(
        \M2E\OnBuy\Model\Account $account,
        \DateTime $borderDate,
        int $creationAttemptsLessThan,
        int $limit
    ): array {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(\M2E\OnBuy\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID, $account->getId());
        $collection->addFieldToFilter(\M2E\OnBuy\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_ID, ['null' => true]);
        $collection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_FAILURE,
            \M2E\OnBuy\Model\Order::MAGENTO_ORDER_CREATION_FAILED_YES,
        );
        $collection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT,
            ['lt' => $creationAttemptsLessThan],
        );
        $collection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE,
            ['lt' => $borderDate->format('Y-m-d H:i:s')],
        );
        $collection->getSelect()->order(
            \M2E\OnBuy\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE . ' ASC'
        );
        $collection->setPageSize($limit);

        return array_values($collection->getItems());
    }

    public function getUnshippedCountForRange(
        \DateTimeInterface $from,
        \DateTimeInterface $to
    ): int {
        $connection = $this->orderResource->getConnection();

        $orderTable = $this->orderCollectionFactory->create()->getMainTable();
        $orderItemTable = $this->orderItemCollectionFactory->create()->getMainTable();

        $minDate = $from->format('Y-m-d H:i:s');
        $maxDate = $to->format('Y-m-d H:i:s');

        $havingCondition = sprintf(
            'MIN(i.expected_dispatch_date) BETWEEN %s AND %s',
            $connection->quote($minDate),
            $connection->quote($maxDate)
        );

        $select = $connection->select()
                             ->from(['o' => $orderTable], ['order_id' => 'o.id'])
                             ->join(['i' => $orderItemTable], 'i.order_id = o.id', [])
                             ->where('o.order_status = ?', \M2E\OnBuy\Model\Order::STATUS_UNSHIPPED)
                             ->where('i.expected_dispatch_date IS NOT NULL')
                             ->group('o.id')
                             ->having($havingCondition);

        $result = $connection->fetchCol($select);
        return count($result);
    }

    public function getLateUnshippedCount(): int
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        $connection = $this->orderResource->getConnection();

        $orderTable = $this->orderCollectionFactory->create()->getMainTable();
        $orderItemTable = $this->orderItemCollectionFactory->create()->getMainTable();

        $select = $connection->select()
                             ->from(['o' => $orderTable], ['order_id' => 'o.id'])
                             ->join(['i' => $orderItemTable], 'i.order_id = o.id', [])
                             ->where('o.order_status = ?', \M2E\OnBuy\Model\Order::STATUS_UNSHIPPED)
                             ->where('i.expected_dispatch_date IS NOT NULL')
                             ->group('o.id')
                             ->having('MIN(i.expected_dispatch_date) < ?', $currentDate->format('Y-m-d H:i:s'));

        $result = $connection->fetchCol($select);
        return count($result);
    }

    public function getUnshippedCountFrom(\DateTimeInterface $from): int
    {
        $connection = $this->orderResource->getConnection();

        $orderTable = $this->orderCollectionFactory->create()->getMainTable();
        $orderItemTable = $this->orderItemCollectionFactory->create()->getMainTable();

        $select = $connection->select()
                             ->from(['o' => $orderTable], ['order_id' => 'o.id'])
                             ->join(['i' => $orderItemTable], 'i.order_id = o.id', [])
                             ->where('o.order_status = ?', \M2E\OnBuy\Model\Order::STATUS_UNSHIPPED)
                             ->where('i.expected_dispatch_date IS NOT NULL')
                             ->group('o.id')
                             ->having('MIN(i.expected_dispatch_date) >= ?', $from->format('Y-m-d H:i:s'));

        $result = $connection->fetchCol($select);
        return count($result);
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    public function getAmountPoints(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        return $this->getPoints('SUM(price_total)', $from, $to, $isHourlyInterval);
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    public function getQuantityPoints(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        return $this->getPoints('COUNT(*)', $from, $to, $isHourlyInterval);
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    private function getPoints(
        string $valueColumn,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter(\M2E\OnBuy\Model\ResourceModel\Order::COLUMN_ORDER_STATUS, ['in' => [
            \M2E\OnBuy\Model\Order::STATUS_UNSHIPPED,
            \M2E\OnBuy\Model\Order::STATUS_SHIPPED,
            \M2E\OnBuy\Model\Order::STATUS_PARTIALLY_SHIPPED
        ]]);
        $collection->addFieldToFilter(OrderResource::COLUMN_PURCHASE_DATE, [
            'from' => $from->format('Y-m-d H:i:s'),
            'to'   => $to->format('Y-m-d H:i:s')
        ]);

        $select = $collection->getSelect();
        $select->reset('columns');
        $select->columns(
            [
                sprintf(
                    'DATE_FORMAT(%s, "%s") AS date',
                    OrderResource::COLUMN_PURCHASE_DATE,
                    $isHourlyInterval ? '%Y-%m-%d %H' : '%Y-%m-%d'
                ),
                sprintf('%s AS value', $valueColumn),
            ]
        );

        if ($isHourlyInterval) {
            $select->group(sprintf('HOUR(main_table.%s)', OrderResource::COLUMN_PURCHASE_DATE));
        }
        $select->group(sprintf('DAY(main_table.%s)', OrderResource::COLUMN_PURCHASE_DATE));
        $select->order('date');

        $queryData = $select->query()->fetchAll();

        $keyValueData = array_combine(
            array_column($queryData, 'date'),
            array_column($queryData, 'value')
        );

        return $this->makePoint($keyValueData, $from, $to, $isHourlyInterval);
    }

    /**
     * @return \M2E\Core\Model\Dashboard\Sales\Point[]
     */
    private function makePoint(
        array $data,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $isHourlyInterval
    ): array {
        $intervalFormat = $isHourlyInterval ? 'PT1H' : 'P1D';
        $dateFormat = $isHourlyInterval ? 'Y-m-d H' : 'Y-m-d';

        $period = new \DatePeriod(
            $from,
            new \DateInterval($intervalFormat),
            $to
        );

        $points = [];
        foreach ($period as $value) {
            $pointValue = (float)($data[$value->format($dateFormat)] ?? 0);
            $pointDate = clone $value;
            $points[] = new \M2E\Core\Model\Dashboard\Sales\Point(
                $pointValue,
                $pointDate
            );
        }

        return $points;
    }
}
