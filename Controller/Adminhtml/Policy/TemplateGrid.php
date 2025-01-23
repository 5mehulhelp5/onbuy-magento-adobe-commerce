<?php

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

use M2E\OnBuy\Controller\Adminhtml\AbstractTemplate;

class TemplateGrid extends AbstractTemplate
{
    public function execute()
    {
        /** @var \M2E\OnBuy\Block\Adminhtml\Template\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Template\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
