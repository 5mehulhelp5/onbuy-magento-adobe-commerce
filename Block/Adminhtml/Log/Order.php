<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Log;

class Order extends \M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $orderRepository,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->_controller = 'adminhtml_log_order';

        $this->setId('onbuyOrderLog');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('magento_order_failed')) {
            $message = __(
                'This Log contains information about your recent %channel_title orders for ' .
                'which Magento orders were not created.<br/><br/>Find detailed info in ' .
                '<a href="%url%" target="_blank">the article</a>.',
                [
                    'url' => 'https://docs-m2.m2epro.com/docs/m2e-onbuy-logs-events/',
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ]
            );
        } else {
            $message = __(
                'This Log contains information about Order processing.<br/><br/>' .
                'Find detailed info in <a href="%url" target="_blank">the article</a>.',
                ['url' => 'https://docs-m2.m2epro.com/docs/m2e-onbuy-logs-events/']
            );
        }
        $helpBlock = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\HelpBlock::class)
                          ->setData([
                              'content' => $message,
                          ]);

        $filtersHtml = $this->getFiltersHtml();

        if (!empty($filtersHtml)) {
            $filtersHtml = <<<HTML
<div class="page-main-actions">
    <div class="filter_block">
        {$filtersHtml}
    </div>
</div>
HTML;
        }

        return $helpBlock->toHtml() . $filtersHtml . parent::_toHtml();
    }

    protected function _prepareLayout()
    {
        $this->addChild('grid', \M2E\OnBuy\Block\Adminhtml\Log\Order\Grid::class);

        return parent::_prepareLayout();
    }

    private function getFiltersHtml(): string
    {
        $accountSwitcherBlock = $this->createAccountSwitcherBlock();
        $uniqueMessageFilterBlock = $this->createUniqueMessageFilterBlock();

        $orderId = $this->getRequest()->getParam('id', false);
        $order = $this->orderRepository->find((int)$orderId);

        if ($orderId && $order !== null) {
            $accountTitle = $this->filterManager->truncate(
                $order->getAccount()->getTitle(),
                ['length' => 15]
            );

            return
                $this->getStaticFilterHtml(
                    $accountSwitcherBlock->getLabel(),
                    $accountTitle
                );
        }

        return $accountSwitcherBlock->toHtml()
            . $uniqueMessageFilterBlock->toHtml();
    }

    private function getStaticFilterHtml(string $label, string $value): string
    {
        return <<<HTML
<p class="static-switcher">
    <span>$label:</span>
    <span>$value</span>
</p>
HTML;
    }

    private function createAccountSwitcherBlock(): \M2E\OnBuy\Block\Adminhtml\Account\Switcher
    {
        return $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Switcher::class);
    }

    private function createUniqueMessageFilterBlock(): \M2E\OnBuy\Block\Adminhtml\Log\UniqueMessageFilter
    {
        return $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Log\UniqueMessageFilter::class)
                    ->setData(
                        [
                            'route' => '*/log_order/',
                            'title' => __('Only messages with a unique Order ID'),
                        ]
                    );
    }
}
