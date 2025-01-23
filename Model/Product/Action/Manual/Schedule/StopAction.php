<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Manual\Schedule;

class StopAction extends AbstractSchedule
{
    use \M2E\OnBuy\Model\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\OnBuy\Model\Product::ACTION_STOP;
    }

    protected function calculateAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator
    ): \M2E\OnBuy\Model\Product\Action {
        return \M2E\OnBuy\Model\Product\Action::createStop($product);
    }

    protected function logAboutSkipAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\OnBuy\Model\Listing\Log::ACTION_STOP_PRODUCT,
            null,
            $this->createSkipStopMessage(),
            \M2E\OnBuy\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
