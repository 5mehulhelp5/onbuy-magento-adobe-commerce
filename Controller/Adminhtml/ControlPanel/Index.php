<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel;

class Index extends AbstractMain
{
    public function execute()
    {
        $this->init();

        $block = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs::class, '');
        $block->setData('tab', 'summary');
        $this->addContent($block);

        return $this->getResult();
    }
}
