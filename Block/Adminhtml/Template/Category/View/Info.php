<?php

namespace M2E\OnBuy\Block\Adminhtml\Template\Category\View;

class Info extends \M2E\OnBuy\Block\Adminhtml\Widget\Info
{
    private \M2E\OnBuy\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary $dictionary,
        \Magento\Framework\Math\Random $random,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($random, $context, $data);

        $this->dictionary = $dictionary;
    }

    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Site'),
                    'value' => $this->dictionary->getSite()->getName()
                ],
                [
                    'label' => __('Category'),
                    'value' => $this->dictionary->getPathWithCategoryId()
                ]
            ]
        );

        return parent::_prepareLayout();
    }

    /*
     * To get "Category" block in center of screen
     */
    public function getInfoPartWidth($index)
    {
        if ($index === 0) {
            return '33%';
        }

        return '66%';
    }

    public function getInfoPartAlign($index)
    {
        return 'left';
    }
}
