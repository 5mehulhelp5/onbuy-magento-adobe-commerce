<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Product\Component\Unmanaged\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Opc extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $opc = $row['opc'];
            $url = $row['product_url'];

            $row['opc'] =  sprintf('<a href="%s" target="_blank">%s</a>', $url, $opc);
        }

        return $dataSource;
    }
}
