<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Template\Description;

use M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock;

class Preview extends AbstractBlock
{
    protected $_template = 'template/description/preview.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->css->addFile('onbuy/template.css');
    }
}
