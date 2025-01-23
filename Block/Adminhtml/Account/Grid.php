<?php

namespace M2E\OnBuy\Block\Adminhtml\Account;

class Grid extends \M2E\OnBuy\Block\Adminhtml\Account\AbstractGrid
{
    private \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Helper\View $viewHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($viewHelper, $context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->js->add(
            <<<JS
    require([
        'OnBuy/Account'
    ], function(){
        window.OnBuyAccountObj = new OnBuyAccount();
    });
JS
        );
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'id',
            'filter_index' => 'main_table.id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title / Info'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => true,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        return parent::_prepareColumns();
    }

    public function callbackColumnTitle($value, $row, $column, $isExport): string
    {
        return <<<HTML
        <div>
            {$value}
        </div>
HTML;
    }

    public function callbackColumnActions($value, $row, $column, $isExport): string
    {
        $delete = __('Delete');

        return <<<HTML
<div>
    <a class="action-default" href="javascript:" onclick="OnBuyAccountObj.deleteClick('{$row->getId()}')">
        {$delete}
    </a>
</div>
HTML;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.title LIKE ?', '%' . $value . '%');
    }

    public function getRowUrl($item): string
    {
        return $this->getUrl('*/*/edit', ['id' => $item->getData('id')]);
    }
}
