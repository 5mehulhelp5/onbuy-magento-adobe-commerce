<?php

namespace M2E\OnBuy\Block\Adminhtml\Template;

use M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractGrid;
use Magento\Framework\DB\Select;
use M2E\OnBuy\Model\ResourceModel\Account as AccountResource;

class Grid extends AbstractGrid
{
    private \M2E\OnBuy\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat\CollectionFactory $sellingCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization\CollectionFactory $syncCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Policy\Shipping\CollectionFactory $shippingCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Policy\Description\CollectionFactory $descriptionCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Account $accountResource;
    private \M2E\OnBuy\Model\ResourceModel\Site $siteResource;
    private \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory;
    /** @var \M2E\OnBuy\Model\ResourceModel\Account\Collection */
    private AccountResource\Collection $enabledAccountCollection;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Site $siteResource,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Model\ResourceModel\Account $accountResource,
        \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat\CollectionFactory $sellingCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization\CollectionFactory $syncCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Policy\Shipping\CollectionFactory $shippingCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Policy\Description\CollectionFactory $descriptionCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->wrapperCollectionFactory = $wrapperCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->accountResource = $accountResource;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->shippingCollectionFactory = $shippingCollectionFactory;
        $this->sellingCollectionFactory = $sellingCollectionFactory;
        $this->syncCollectionFactory = $syncCollectionFactory;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
        $this->siteResource = $siteResource;
        $this->siteRepository = $siteRepository;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->css->addFile('policy/grid.css');

        // Initialization block
        // ---------------------------------------
        $this->setId('onbuyTemplateGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        // Prepare selling format collection
        // ---------------------------------------
        $collectionSellingFormat = $this->sellingCollectionFactory->create();
        $collectionSellingFormat->getSelect()->reset(Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('NULL as `account_title`'),
                new \Zend_Db_Expr('\'0\' as `account_id`'),
                new \Zend_Db_Expr('NULL as `site_title`'),
                new \Zend_Db_Expr('\'0\' as `site_id`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT . '\' as `nick`'
                ),
                'create_date',
                'update_date',
            ]
        );

        // ---------------------------------------

        // Prepare synchronization collection
        // ---------------------------------------
        $collectionSynchronization = $this->syncCollectionFactory->create();
        $collectionSynchronization->getSelect()->reset(Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('NULL as `account_title`'),
                new \Zend_Db_Expr('\'0\' as `account_id`'),
                new \Zend_Db_Expr('NULL as `site_title`'),
                new \Zend_Db_Expr('\'0\' as `site_id`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SYNCHRONIZATION . '\' as `nick`'
                ),
                'create_date',
                'update_date',
            ]
        );

        // Prepare Shipping collection
        // ----------------------------------------
        $collectionShipping = $this->shippingCollectionFactory->create();
        $collectionShipping->getSelect()->reset(Select::COLUMNS);
        $collectionShipping->getSelect()->join(
            ['account' => $this->accountResource->getMainTable()],
            sprintf(
                'account.%s = main_table.%s',
                \M2E\OnBuy\Model\ResourceModel\Account::COLUMN_ID,
                \M2E\OnBuy\Model\ResourceModel\Policy\Shipping::COLUMN_ACCOUNT_ID
            ),
            []
        );
        $collectionShipping->getSelect()->join(
            ['site' => $this->siteResource->getMainTable()],
            sprintf(
                'site.%s = main_table.%s',
                \M2E\OnBuy\Model\ResourceModel\Site::COLUMN_ID,
                \M2E\OnBuy\Model\ResourceModel\Policy\Shipping::COLUMN_SITE_ID
            ),
            []
        );

        $collectionShipping->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('account.title as `account_title`'),
                new \Zend_Db_Expr('account.id as `account_id`'),
                new \Zend_Db_Expr('site.name as `site_title`'),
                new \Zend_Db_Expr('site.site_id as `site_id`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SHIPPING . '\' as `nick`'
                ),
                'create_date',
                'update_date',
            ]
        );

        ///Prepare Description collection
        $collectionDescription = $this->descriptionCollectionFactory->create();
        $collectionDescription->getSelect()->reset(Select::COLUMNS);
        $collectionDescription->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('NULL as `account_title`'),
                new \Zend_Db_Expr('\'0\' as `account_id`'),
                new \Zend_Db_Expr('NULL as `site_title`'),
                new \Zend_Db_Expr('\'0\' as `site_id`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_DESCRIPTION . '\' as `nick`'
                ),
                'create_date',
                'update_date',
            ]
        );

        // Prepare union select
        // ---------------------------------------
        $unionSelect = $this->resourceConnection->getConnection()->select();
        $unionSelect->union([
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionShipping->getSelect(),
            $collectionDescription->getSelect(),
        ]);

        // Prepare result collection
        // ---------------------------------------
        $resultCollection = $this->wrapperCollectionFactory->create();
        $resultCollection->setConnection($this->resourceConnection->getConnection());
        $resultCollection->getSelect()->reset()->from(
            ['main_table' => $unionSelect],
            [
                'template_id',
                'title',
                'account_title',
                'account_id',
                'site_title',
                'site_id',
                'nick',
                'create_date',
                'update_date',
            ]
        );

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => true,
            'filter_index' => 'main_table.title',
        ]);

        $options = [
            \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT => __('Selling'),
            \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SYNCHRONIZATION => __('Synchronization'),
            \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SHIPPING => __('Shipping'),
            \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_DESCRIPTION => __('Description'),
        ];
        $this->addColumn('nick', [
            'header' => __('Type'),
            'align' => 'left',
            'type' => 'options',
            'width' => '100px',
            'sortable' => false,
            'index' => 'nick',
            'filter_index' => 'main_table.nick',
            'options' => $options,
        ]);

        $this->addColumn('account', [
            'header' => $this->__('Account'),
            'align' => 'left',
            'type' => 'options',
            'width' => '100px',
            'index' => 'account_title',
            'filter_index' => 'account_title',
            'filter_condition_callback' => [$this, 'callbackFilterAccount'],
            'frame_callback' => [$this, 'callbackColumnAccount'],
            'options' => $this->getEnabledAccountTitles(),
        ]);

        $this->addColumn('site', [
            'header' => $this->__('Site'),
            'align' => 'left',
            'type' => 'options',
            'width' => '100px',
            'index' => 'site_id',
            'filter_index' => 'site_id',
            'filter_condition_callback' => [$this, 'callbackFilterSite'],
            'frame_callback' => [$this, 'callbackColumnSite'],
            'options' => $this->getEnabledSiteTitles(),
        ]);

        $this->addColumn('create_date', [
            'header' => (string)__('Creation Date'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'filter_time' => true,
            'format' => \IntlDateFormatter::MEDIUM,
            'index' => 'create_date',
            'filter_index' => 'main_table.create_date',
        ]);

        $this->addColumn('update_date', [
            'header' => (string)__('Update Date'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'filter_time' => true,
            'format' => \IntlDateFormatter::MEDIUM,
            'index' => 'update_date',
            'filter_index' => 'main_table.update_date',
        ]);

        $this->addColumn('actions', [
            'header' => __('Actions'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'renderer' => \M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'getter' => 'getTemplateId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'url' => [
                        'base' => '*/policy/edit',
                        'params' => [
                            'nick' => '$nick',
                        ],
                    ],
                    'field' => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'class' => 'action-default scalable add primary policy-delete-btn',
                    'url' => [
                        'base' => '*/policy/delete',
                        'params' => [
                            'nick' => '$nick',
                        ],
                    ],
                    'field' => 'id',
                    'confirm' => __('Are you sure?'),
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/templateGrid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl(
            '*/policy/edit',
            [
                'id' => $item->getData('template_id'),
                'nick' => $item->getData('nick'),
                'back' => 1,
            ]
        );
    }

    protected function callbackFilterAccount($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('account_id = 0 OR account_id = ?', (int)$value);
    }

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return __('Any');
        }

        return $value;
    }

    public function callbackColumnSite($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return __('Any');
        }

        $parts = explode(' ', $value, 2);

        if (isset($parts[1])) {
            return $parts[1];
        }

        return $value;
    }

    protected function callbackFilterSite($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('site_id = 0 OR site_id = ?', (int)$value);
    }

    private function getEnabledAccountCollection(): AccountResource\Collection
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->enabledAccountCollection)) {
            $collection = $this->accountCollectionFactory->create();
            $collection->setOrder(AccountResource::COLUMN_TITLE, 'ASC');

            $this->enabledAccountCollection = $collection;
        }

        return $this->enabledAccountCollection;
    }

    private function getEnabledAccountTitles(): array
    {
        $result = [];
        foreach ($this->getEnabledAccountCollection()->getItems() as $account) {
            $result[$account->getId()] = $account->getTitle();
        }

        return $result;
    }

    private function getEnabledSiteTitles(): array
    {
        $result = [];
        foreach ($this->siteRepository->getAllGroupBySiteId() as $site) {
            $parts = explode(' ', $site->getName(), 2);

            if (isset($parts[1])) {
                $result[$site->getSiteId()] = $parts[1];
            } else {
                $result[$site->getSiteId()] = $site->getName();
            }
        }

        return $result;
    }
}
