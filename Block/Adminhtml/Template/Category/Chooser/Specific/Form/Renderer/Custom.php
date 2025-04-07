<?php

namespace M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Specific\Form\Renderer;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element as MagentoElement;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Custom extends MagentoElement
{
    public \Magento\Framework\View\LayoutInterface $layout;

    protected $element;

    public function __construct(
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->layout = $context->getLayout();
        $this->setTemplate('template/category/chooser/specific/form/renderer/custom.phtml');
    }

    public function getElement()
    {
        return $this->element;
    }

    public function render(AbstractElement $element)
    {
        $this->element = $element;

        return $this->toHtml();
    }
}
