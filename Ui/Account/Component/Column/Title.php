<?php

declare(strict_types=1);

namespace M2E\OnBuy\Ui\Account\Component\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Title extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $accountTitle = $row['title'];

            $html = \M2E\Core\Helper\Data::escapeHtml($accountTitle);
            if ($row['is_test']) {
                $html .= ' <span style="color:orange">(Test)</span>';
            }

            $row['title'] = $html;
        }

        return $dataSource;
    }
}
