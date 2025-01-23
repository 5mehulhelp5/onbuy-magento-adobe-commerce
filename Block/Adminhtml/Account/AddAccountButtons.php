<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Account;

class AddAccountButtons implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper
    ) {
        $this->accountUrlHelper = $accountUrlHelper;
    }

    public function getButtonData()
    {
        return [
            'label' => __('Add Account'),
            'class' => 'action-primary action-btn',
            'on_click' => '',
            'sort_order' => 4,
            'data_attribute' => [
                'mage-init' => [
                    'OnBuy/Account/AddButton' => [
                        'urlCreate' => $this->accountUrlHelper->getCreateUrl(),
                    ],
                ],
            ],
        ];
    }
}
