<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Renderer;

class Opc extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number
{
    use \M2E\OnBuy\Block\Adminhtml\Traits\BlockTrait;

    public function render(\Magento\Framework\DataObject $row)
    {
        $opc = $row->getData('opc');
        $url = $row->getData('online_product_url');

        $creator = $row->getData('is_product_creator') ?
            '<br><span style="font-size: 10px; color: grey;">' . __('Product Creator') . '</span>' : '';

        return '<a href="' . $url . '" target="_blank">' . $opc . '</a>' . $creator;
    }

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        return $this->_getValue($row) ?? '';
    }
}
