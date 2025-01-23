<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\UnmanagedProduct\Grid;

use M2E\OnBuy\Model\ResourceModel\Site as SiteResource;
use M2E\OnBuy\Model\ResourceModel\UnmanagedProduct;
use Magento\Framework\Event\ManagerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Api\Search\SearchResultInterface
{
    use \M2E\OnBuy\Model\ResourceModel\SearchResultTrait;

    protected $_idFieldName = 'id';
    private \M2E\OnBuy\Model\ResourceModel\Site $siteResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Site $siteResource,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->siteResource = $siteResource;
        $this->prepareCollection();
    }

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct::class,
        );
    }

    private function prepareCollection(): void
    {
        $this->join(
            ['site' => $this->siteResource->getMainTable()],
            sprintf(
                'main_table.%s = site.%s',
                UnmanagedProduct::COLUMN_SITE_ID,
                SiteResource::COLUMN_ID
            ),
            ['site_' . SiteResource::COLUMN_SITE_ID => SiteResource::COLUMN_SITE_ID]
        );
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'account') {
            $field = 'main_table.account_id';
        }

        if ($field === 'site_id') {
            $field = 'site.site_id';
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }
}
