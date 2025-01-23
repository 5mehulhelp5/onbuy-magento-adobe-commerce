<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database;

class DatabaseTableGrid extends AbstractTable
{
    public function execute()
    {
        /** @var \M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Grid $grid */
        $grid = $this->getLayout()
                     ->createBlock(
                         \M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Grid::class,
                         '',
                         [
                             'tableName' => $this->getRequest()->getParam('table'),
                         ],
                     );
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
