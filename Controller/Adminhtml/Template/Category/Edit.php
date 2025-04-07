<?php

namespace M2E\OnBuy\Controller\Adminhtml\Template\Category;

class Edit extends \M2E\OnBuy\Controller\Adminhtml\Template\AbstractCategory
{
    public function execute()
    {
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');

        /** @var \M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Edit::class,
        );

        if (!empty($selectedValue)) {
            $editBlock->setSelectedCategory($selectedValue, $selectedPath);
        }

        $html = $editBlock->toHtml();
        $this->setAjaxContent($html);

        return $this->getResult();
    }
}
