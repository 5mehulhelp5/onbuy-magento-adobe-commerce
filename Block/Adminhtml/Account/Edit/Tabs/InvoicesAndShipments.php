<?php

namespace M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs;

use M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm;

class InvoicesAndShipments extends AbstractForm
{
    private ?\M2E\OnBuy\Model\Account $account;

    public function __construct(
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\OnBuy\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $invoicesAndShipmentSettings = new \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment();
        if ($this->account !== null) {
            $invoicesAndShipmentSettings = $this->account->getInvoiceAndShipmentSettings();
        }

        $form = $this->_formFactory->create();

        $form->addField(
            'invoices_and_shipments',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>Under this tab, you can set %extension_title to automatically create ' .
                    'invoices and shipments in your Magento. To do that, keep Magento ' .
                    '<i>Invoice/Shipment Creation</i> options enabled.</p>',
                    [
                        'extension_title' => \M2E\OnBuy\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'invoices',
            [
                'legend' => __('Invoices'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'create_magento_invoice',
            'select',
            [
                'label' => __('Magento Invoice Creation'),
                'title' => __('Magento Invoice Creation'),
                'name' => 'create_magento_invoice',
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
                'value' => (int)$invoicesAndShipmentSettings->isCreateMagentoInvoice(),
            ]
        );

        $fieldset = $form->addFieldset(
            'shipments',
            [
                'legend' => __('Shipments'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'create_magento_shipment',
            \Magento\Framework\Data\Form\Element\Select::class,
            [
                'label' => __('Magento Shipment Creation'),
                'title' => __('Magento Shipment Creation'),
                'name' => 'create_magento_shipment',
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
                'value' => (int)$invoicesAndShipmentSettings->isCreateMagentoShipment(),

            ]
        );

        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
