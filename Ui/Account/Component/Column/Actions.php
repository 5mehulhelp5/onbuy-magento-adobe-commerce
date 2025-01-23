<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Account\Component\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $urlHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->urlHelper = $urlHelper;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');

                $item[$name]['edit'] = [
                    'href' => $this->urlHelper->getEditUrl((int)$item['id']),
                    'label' => __('Edit')
                ];

                $item[$name]['refresh'] = [
                    'href' => $this->urlHelper->getRefreshUrl((int)$item['id']),
                    'label' => __('Refresh')
                ];

                $item[$name]['delete'] = [
                    'href' => $this->urlHelper->getDeleteUrl((int)$item['id']),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Confirmation'),
                        'message' => \M2E\OnBuy\Model\TranslateText::getAccountDelete()
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
