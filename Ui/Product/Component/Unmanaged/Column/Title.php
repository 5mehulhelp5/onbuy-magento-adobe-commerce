<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Title extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->siteRepository = $siteRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $productTitle = $row['title'];

            $html = sprintf('<p>%s</p>', $productTitle);

            $html .= $this->renderLine((string)\__('SKU'), $row['sku']);
            $siteName = $this->getSiteName((int)$row['site_id']);
            $html .= $this->renderLine((string)\__('Site'), $siteName);

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0"><strong>%s:</strong> %s</p>', $label, $value);
    }

    private function getSiteName(int $siteId): string
    {
        return $this->siteRepository->get($siteId)->getName();
    }
}
