<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Account;

class CredentialsFormFactory
{
    private \Magento\Framework\Data\FormFactory $formFactory;

    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
    }

    public function create(
        bool $withTitle,
        bool $withSellerId,
        bool $withButton,
        string $id,
        string $submitUrl = ''
    ): \Magento\Framework\Data\Form {
        $form = $this->formFactory->create(
            [
                'data' => [
                    'id' => $id,
                    'action' => $submitUrl,
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);

        $fieldset = $form->addFieldset(
            'general_credentials',
            [
                'legend' => __('Add API Keys'),
                'collapsable' => false,
                'class' => 'fieldset admin__fieldset admin__field-control',
            ],
        );

        if ($withTitle) {
            $fieldset->addField(
                'title',
                'text',
                [
                    'name' => 'title',
                    'class' => 'onbuy-account-title',
                    'label' => __('Title'),
                    'value' => '',
                    'required' => true,
                ],
            );
        }

        if ($withSellerId) {
            $fieldset->addField(
                'seller_id',
                'text',
                [
                    'name' => 'seller_id',
                    'class' => 'onbuy-account-title',
                    'label' => __('Seller ID'),
                    'value' => '',
                    'required' => true,
                ],
            );
        }

        $fieldset->addField(
            'client_key',
            'text',
            [
                'name' => 'consumer_key',
                'class' => 'onbuy-account-title',
                'label' => __('Consumer Key'),
                'value' => '',
                'required' => true,
            ],
        );

        $fieldset->addField(
            'secret_key',
            'text',
            [
                'name' => 'secret_key',
                'class' => 'onbuy-account-title',
                'label' => __('Secret Key'),
                'value' => '',
                'required' => true,
            ],
        );

        if ($withButton) {
            $fieldset->addField(
                'submit_button',
                'submit',
                [
                    'value' => __('Save'),
                    'style' => '',
                    'class' => 'submit action-default Onbuy-fieldset field-submit_button',
                ]
            );
        }

        return $form;
    }
}
