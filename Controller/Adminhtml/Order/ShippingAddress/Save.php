<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order\ShippingAddress;

use M2E\OnBuy\Controller\Adminhtml\Order\AbstractOrder;

class Save extends AbstractOrder
{
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $orderRepository
    ) {
        parent::__construct();
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();

        if (!$post->count()) {
            $this->setJsonContent([
                'success' => false,
            ]);

            return $this->getResult();
        }

        $orderId = $this->getRequest()->getParam('id', false);

        $order = $this->orderRepository->get($orderId);

        $data = [];
        $keys = [
            'buyer_name',
            'buyer_email',
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('buyer_name', $data['buyer_name']);
        $order->setData('buyer_email', $data['buyer_email']);

        $data = [];
        $keys = [
            'recipient_name',
            'street',
            'city',
            'country_code',
            'state',
            'postal_code',
            'phone',
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($data['street']) && is_array($data['street'])) {
            $data['street'] = array_filter($data['street']);
        }

        $shippingDetails = $order->getShippingDetails();
        $shippingDetails['address'] = $data;

        $order->setShippingDetails($shippingDetails);
        $order->setBuyerPhone($data['phone']);
        $this->orderRepository->save($order);

        $shippingAddressBlock = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Edit\ShippingAddress::class, '', [
                'order' => $order,
            ]);

        $this->setJsonContent([
            'success' => true,
            'html' => $shippingAddressBlock->toHtml(),
        ]);

        return $this->getResult();
    }
}
