<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database;

/**
 * Class \M2E\OnBuy\Controller\Adminhtml\ControlPanel\Database\GetTableCellsPopupHtml
 */
class GetTableCellsPopupHtml extends AbstractTable
{
    public function execute()
    {
        $block = $this->getLayout()
                      ->createBlock(
                          \M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs\Database\Table\TableCellsPopup::class
                      );
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
