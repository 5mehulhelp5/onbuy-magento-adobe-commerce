<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Price extends Column
{
    private CurrencyInterface $localeCurrency;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->siteRepository = $siteRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $price = $row['price'];

            try {
                $currencyCode = $this->siteRepository->get((int)$row['site_id'])->getCurrencyCode();
            } catch (\M2E\OnBuy\Model\Exception\Logic $e) {
                continue;
            }

            $price = $this->localeCurrency->getCurrency($currencyCode)->toCurrency($price);

            $row['price'] = $price;
        }

        return $dataSource;
    }
}
