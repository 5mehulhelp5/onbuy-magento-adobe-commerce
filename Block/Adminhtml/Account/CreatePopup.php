<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Account;

class CreatePopup extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock
{
    private \Magento\Framework\View\Page\Config $config;
    private \M2E\OnBuy\Block\Adminhtml\Account\CredentialsFormFactory $credentialsFormFactory;

    public function __construct(
        \Magento\Framework\View\Page\Config $config,
        \M2E\OnBuy\Block\Adminhtml\Account\CredentialsFormFactory $credentialsFormFactory,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->config = $config;
        $this->credentialsFormFactory = $credentialsFormFactory;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->config->addPageAsset('M2E_OnBuy::css/account/credentials.css');
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\OnBuy\Block\Adminhtml\Account\Edit\Form::class);

        return parent::_prepareLayout();
    }

    protected function _toHtml(): string
    {
        return parent::_toHtml()
            . '<div class="custom-popup" style="display: none;">'
            . $this->credentialsFormFactory->create(
                true,
                true,
                true,
                'account_credentials'
            )->toHtml()
            . '</div>';
    }
}
