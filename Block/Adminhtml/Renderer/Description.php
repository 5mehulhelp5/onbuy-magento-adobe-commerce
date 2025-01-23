<?php

namespace M2E\OnBuy\Block\Adminhtml\Renderer;

use M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\OnBuy\Block\Adminhtml\Renderer\Description
 */
abstract class Description extends AbstractBlock
{
    //########################################

    /**
     * We can not use \Magento\Store\Model\App\Emulation. Environment emulation is already started into Description
     * Renderer and can not be emulated again
     * @return string
     */
    protected function _toHtml()
    {
        $this->setData('area', \Magento\Framework\App\Area::AREA_ADMINHTML);

        return parent::_toHtml();
    }

    //########################################
}
