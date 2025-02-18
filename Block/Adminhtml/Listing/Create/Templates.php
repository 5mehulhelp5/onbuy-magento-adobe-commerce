<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing\Create;

use M2E\OnBuy\Model\Listing;

class Templates extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\OnBuy\Helper\Data\Session $sessionDataHelper;
    public function __construct(
        \M2E\OnBuy\Helper\Data\Session $sessionDataHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('onbuyListingCreateTemplates');
        $this->_controller = 'adminhtml_listing_create';
        $this->_mode = 'templates';

        $this->_headerText = __('Creating A New Listing');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/listing_create/index',
            ['_current' => true, 'step' => 1]
        );
        $this->addButton(
            'back',
            [
                'label' => __('Previous Step'),
                'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
                'class' => 'back',
            ]
        );

        $nextStepBtnText = 'Next Step';

        $sessionData = $this->sessionDataHelper->getValue(
            Listing::CREATE_LISTING_SESSION_DATA
        );
        if (
            isset($sessionData['creation_mode']) && $sessionData['creation_mode'] ===
            \M2E\OnBuy\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY
        ) {
            $nextStepBtnText = 'Complete';
        }

        $url = $this->getUrl(
            '*/listing_create/index',
            ['_current' => true]
        );

        $this->addButton(
            'save',
            [
                'label' => $nextStepBtnText,
                'onclick' => 'CommonObj.saveClick(\'' . $url . '\')',
                'class' => 'action-primary',
            ]
        );
    }

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()
                           ->createBlock(\M2E\OnBuy\Block\Adminhtml\Listing\Create\Breadcrumb::class);
        $breadcrumb->setSelectedStep(2);

        $helpBlock = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\HelpBlock::class);
        $helpBlock->addData(
            [
                'content' => __(
                    '<p>In this Section, you can choose the right listing format, provide a ' .
                    'competitive price for your Items, and set the preferences on how to ' .
                    'synchronize your Items with Magento Catalog data.</p> <p>There is a <b>Selling</b> ' .
                    'policy to configure the QTY and Price settings. ' .
                    'The synch rules can be defined in the <b>Synchronization policy</b>. </p>' .
                    '<p>More details in ' .
                    '<a href="%url" target="_blank">our documentation</a>.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/docs/create-m2e-onbuy-listing/']
                ),
                'style' => 'margin-top: 30px',
            ]
        );

        return
            $breadcrumb->_toHtml() .
            '<div id="progress_bar"></div>' .
            $helpBlock->toHtml() .
            '<div id="content_container">' . parent::_toHtml() . '</div>';
    }
}
