<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Manual\Schedule;

class RelistAction extends AbstractSchedule
{
    use \M2E\OnBuy\Model\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\OnBuy\Model\Product::ACTION_RELIST;
    }

    protected function calculateAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator
    ): \M2E\OnBuy\Model\Product\Action {
        return $calculator->calculateToRelist($product, \M2E\OnBuy\Model\Product::STATUS_CHANGER_USER);
    }

    protected function logAboutSkipAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\OnBuy\Model\Listing\Log::ACTION_RELIST_PRODUCT,
            null,
            $this->createSkipRelistMessage(),
            \M2E\OnBuy\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
