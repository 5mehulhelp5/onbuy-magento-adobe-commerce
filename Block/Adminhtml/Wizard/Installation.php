<?php

namespace M2E\OnBuy\Block\Adminhtml\Wizard;

abstract class Installation extends AbstractWizard
{
    /** @var \M2E\OnBuy\Helper\Data */
    private $dataHelper;

    /**
     * @param \M2E\OnBuy\Helper\Data $dataHelper
     * @param \M2E\OnBuy\Helper\Module\Wizard $wizardHelper
     * @param \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context
     * @param array $data
     */
    public function __construct(
        \M2E\OnBuy\Helper\Data $dataHelper,
        \M2E\OnBuy\Helper\Module\Wizard $wizardHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($dataHelper, $wizardHelper, $context, $data);
    }

    abstract protected function getStep();

    protected function _construct()
    {
        parent::_construct();

        $this->addButton(
            'continue',
            [
                'label' => __('Continue'),
                'class' => 'primary forward',
            ],
            1,
            0
        );
    }

    protected function _beforeToHtml()
    {
        $this->setId('wizard' . $this->getNick() . $this->getStep());

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $this->jsUrl->addUrls([
            'wizard_registration/createLicense' => $this->getUrl('*/wizard_registration/createLicense'),
            'wizard_installationOnBuy/accountCreate' => $this->getUrl('m2e_onbuy/wizard_installationOnBuy/accountCreate/'),
            'wizard_installationOnBuy/settingsContinue' => $this->getUrl('m2e_onbuy/wizard_installationOnBuy/settingsContinue/'),
        ]);

        $stepsBlock = $this->getLayout()->createBlock(
            $this->nameBuilder->buildClassName(
                [
                    '\M2E\OnBuy\Block\Adminhtml\Wizard',
                    $this->getNick(),
                    'Breadcrumb',
                ]
            )
        )->setSelectedStep($this->getStep());

        $helpBlock = $this->getLayout()
                          ->createBlock(\M2E\OnBuy\Block\Adminhtml\HelpBlock::class, 'wizard.help.block')
                          ->setData(
                              [
                                  'no_collapse' => true,
                                  'no_hide' => true,
                              ]
                          );

        $contentBlock = $this->getLayout()->createBlock(
            $this->nameBuilder->buildClassName(
                [
                    '\M2E\OnBuy\Block\Adminhtml\Wizard',
                    $this->getNick(),
                    'Installation',
                    $this->getStep(),
                    'Content',
                ]
            )
        )->setData(
            [
                'nick' => $this->getNick(),
            ]
        );

        return parent::_toHtml() .
            $stepsBlock->toHtml() .
            $helpBlock->toHtml() .
            $contentBlock->toHtml();
    }
}
