<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Manual\Schedule;

use M2E\OnBuy\Model\Product\Action\Manual\Schedule\AbstractSchedule;

class ListAction extends AbstractSchedule
{
    use \M2E\OnBuy\Model\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\OnBuy\Model\Product::ACTION_LIST;
    }

    protected function calculateAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator
    ): \M2E\OnBuy\Model\Product\Action {
        return $calculator->calculateToList($product);
    }

    protected function logAboutSkipAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\OnBuy\Model\Listing\Log::ACTION_LIST_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipListMessage(),
            \M2E\OnBuy\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
