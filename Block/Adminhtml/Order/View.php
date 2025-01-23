<?php

namespace M2E\OnBuy\Block\Adminhtml\Order;

use M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractContainer;

class View extends AbstractContainer
{
    /** @var \M2E\OnBuy\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\Core\Helper\Url $urlHelper;

    public function __construct(
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('onbuyOrderView');
        $this->_controller = 'adminhtml_onBuy_order';
        $this->_mode = 'view';

        /** @var \M2E\OnBuy\Model\Order $order */
        $order = $this->globalDataHelper->getValue('order');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->urlHelper->getBackUrl('*/order/index');
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
            'class' => 'back',
        ]);

        if ($order->getReserve()->isPlaced()) {
            $url = $this->getUrl('*/order/reservationCancel', ['ids' => $order->getId()]);
            $this->addButton('reservation_cancel', [
                'label' => __('Cancel QTY Reserve'),
                'onclick' => "confirmSetLocation(OnBuy.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        } elseif ($order->isReservable()) {
            $url = $this->getUrl('*/order/reservationPlace', ['ids' => $order->getId()]);
            $this->addButton('reservation_place', [
                'label' => __('Reserve QTY'),
                'onclick' => "confirmSetLocation(OnBuy.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        }

        if ($order->canCreateMagentoOrder()) {
            $url = $this->getUrl('*/order/createMagentoOrder', ['id' => $order->getId()]);
            $this->addButton('order', [
                'label' => __('Create Magento Order'),
                'onclick' => "setLocation('" . $url . "');",
                'class' => 'primary',
            ]);
        }
    }

    protected function _beforeToHtml()
    {
        $this->js->addRequireJs(['debug' => 'OnBuy/Order/Debug'], '');

        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\OnBuy\Block\Adminhtml\Order\View\Form::class);

        return parent::_prepareLayout();
    }
}
