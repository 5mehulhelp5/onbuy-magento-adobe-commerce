<?php

namespace M2E\OnBuy\Block\Adminhtml\Order\Edit\ShippingAddress;

use M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm;

class Form extends AbstractForm
{
    private \M2E\Core\Helper\Magento $magentoHelper;
    private \M2E\OnBuy\Model\Order $order;

    public function __construct(
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\OnBuy\Model\Order $order,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->magentoHelper = $magentoHelper;
        $this->order = $order;
    }

    protected function _prepareForm()
    {
        $order = $this->order;

        $buyerEmail = $order->getBuyerEmail();
        if (stripos($buyerEmail, 'Invalid Request') !== false) {
            $buyerEmail = '';
        }

        try {
            $regionCode = $order->getShippingAddress()->getRegionCode();
        } catch (\Exception $e) {
            $regionCode = null;
        }

        $state = $order->getShippingAddress()->getState();

        if (empty($regionCode) && !empty($state)) {
            $regionCode = $state;
        }

        $address = $order->getShippingAddress()->getData();

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'order_address_info',
            [
                'legend' => __('Order Address Information'),
            ]
        );

        $fieldset->addField(
            'buyer_name',
            'text',
            [
                'name' => 'buyer_name',
                'label' => __('Buyer Name'),
                'value' => $order->getBuyerName(),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'buyer_email',
            'text',
            [
                'name' => 'buyer_email',
                'label' => __('Buyer Email'),
                'value' => $buyerEmail,
                'required' => true,
            ]
        );

        $fieldset->addField(
            'recipient_name',
            'text',
            [
                'name' => 'recipient_name',
                'label' => __('Recipient Name'),
                'value' => isset($address['recipient_name'])
                    ? \M2E\Core\Helper\Data::escapeHtml($address['recipient_name']) : '',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'street_0',
            'text',
            [
                'name' => 'street[0]',
                'label' => __('Street Address'),
                'value' => isset($address['street'][0])
                    ? \M2E\Core\Helper\Data::escapeHtml($address['street'][0]) : '',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'street_1',
            'text',
            [
                'name' => 'street[1]',
                'label' => '',
                'value' => isset($address['street'][1])
                    ? \M2E\Core\Helper\Data::escapeHtml($address['street'][1]) : '',
            ]
        );

        $fieldset->addField(
            'street_2',
            'text',
            [
                'name' => 'street[2]',
                'label' => '',
                'value' => isset($address['street'][2])
                    ? \M2E\Core\Helper\Data::escapeHtml($address['street'][2]) : '',
            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'value' => $address['city'],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'country_code',
            'select',
            [
                'name' => 'country_code',
                'label' => __('Country'),
                'values' => $this->magentoHelper->getCountries(),
                'value' => $address['country_code'],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'state',
            'text',
            [
                'container_id' => 'state_td',
                'label' => __('Region/State'),
            ]
        );

        $fieldset->addField(
            'postal_code',
            'text',
            [
                'name' => 'postal_code',
                'label' => __('Zip/Postal Code'),
                'value' => $address['postal_code'],
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Telephone'),
                'value' => $order->getBuyerPhone(),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->jsUrl->addUrls([
            'order/assignProduct' => $this->getUrl('m2e_onbuy/order/assignProduct/'),
            'order/assignProductDetails' => $this->getUrl('m2e_onbuy/order/assignProductDetails/'),
            'order/assignToMagentoProduct' => $this->getUrl('m2e_onbuy/order/assignToMagentoProduct/'),
            'order/checkProductOptionStockAvailability' => $this->getUrl('m2e_onbuy/order/checkProductOptionStockAvailability/'),
            'order/createMagentoOrder' => $this->getUrl('m2e_onbuy/order/createMagentoOrder/'),
            'order/deleteNote' => $this->getUrl('m2e_onbuy/order/deleteNote/'),
            'order/getCountryRegions' => $this->getUrl('m2e_onbuy/order/getCountryRegions/'),
            'order/getDebugInformation' => $this->getUrl('m2e_onbuy/order/getDebugInformation/'),
            'order/getNotePopupHtml' => $this->getUrl('m2e_onbuy/order/getNotePopupHtml/'),
            'order/index' => $this->getUrl('m2e_onbuy/order/index/'),
            'order/reservationCancel' => $this->getUrl('m2e_onbuy/order/reservationCancel/'),
            'order/reservationPlace' => $this->getUrl('m2e_onbuy/order/reservationPlace/'),
            'order/resubmitShippingInfo' => $this->getUrl('m2e_onbuy/order/resubmitShippingInfo/'),
            'order/saveNote' => $this->getUrl('m2e_onbuy/order/saveNote/'),
            'order/shippingAddress' => $this->getUrl('m2e_onbuy/order/shippingAddress/'),
            'order/unAssignFromMagentoProduct' => $this->getUrl('m2e_onbuy/order/unAssignFromMagentoProduct/'),
            'order/view' => $this->getUrl('m2e_onbuy/order/view/'),
        ]);

        $this->jsUrl->add(
            $this->getUrl(
                '*/onbuy_order_shippingAddress/save',
                ['order_id' => $this->getRequest()->getParam('id')]
            ),
            'formSubmit'
        );

        $this->js->add("OnBuy.formData.region = '" . \M2E\Core\Helper\Data::escapeJs($regionCode) . "';");

        $this->js->add(
            <<<JS
    require([
        'OnBuy/Order/Edit/ShippingAddress',
    ], function(){
        window.OrderEditShippingAddressObj = new OrderEditShippingAddress('country_code', 'state_td', 'state');
        OrderEditShippingAddressObj.initObservers();
    });
JS
        );

        return parent::_prepareForm();
    }
}
