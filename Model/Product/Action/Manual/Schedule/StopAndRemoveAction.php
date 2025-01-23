<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Manual\Schedule;

use M2E\OnBuy\Model\Product\Action\Manual\Schedule\AbstractSchedule;

class StopAndRemoveAction extends AbstractSchedule
{
    private \M2E\OnBuy\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\OnBuy\Model\Product\RemoveHandler $removeHandler,
        \M2E\OnBuy\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator,
        \M2E\OnBuy\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct($scheduledActionCreateService, $calculator, $listingLogService);
        $this->removeHandler = $removeHandler;
    }

    protected function getAction(): int
    {
        return \M2E\OnBuy\Model\Product::ACTION_DELETE;
    }

    protected function prepareOrFilterProducts(array $listingsProducts): array
    {
        $result = [];
        foreach ($listingsProducts as $listingProduct) {
            if ($listingProduct->isStoppable()) {
                $result[] = $listingProduct;

                continue;
            }

            $this->removeHandler->process(
                $listingProduct,
                \M2E\Core\Helper\Data::INITIATOR_USER
            );
        }

        return $result;
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
    }
}
