<?php

namespace M2E\OnBuy\Block\Adminhtml\Order\Grid\Column\Renderer;

class DateTime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime
{
    use \M2E\OnBuy\Block\Adminhtml\Traits\BlockTrait;

    public function render(\Magento\Framework\DataObject $row): string
    {
        return $this->renderGeneral($row, false);
    }

    public function renderExport(\Magento\Framework\DataObject $row): string
    {
        return $this->renderGeneral($row, true);
    }

    public function renderGeneral(\Magento\Framework\DataObject $row, bool $isExport): string
    {
        $value = parent::render($row);

        if ($row->getData('status') == \M2E\OnBuy\Model\Product::STATUS_NOT_LISTED) {
            if ($isExport) {
                return '';
            }

            return '<span style="color: gray;">' . __('Not Listed') . '</span>';
        }

        return $value;
    }
}
