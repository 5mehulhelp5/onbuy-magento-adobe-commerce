<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Template\Category;

class ChangeProcessor extends \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_category_change_processor';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    protected function getInstructionsData(
        \M2E\OnBuy\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];
        /** @var \M2E\OnBuy\Model\Template\Category\Diff $diff */
        if ($diff->isDifferent()) {
            $data[] = [
                'type' => \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'priority' => $status === \M2E\OnBuy\Model\Product::STATUS_LISTED ? 30 : 5,//TODO: check!
            ];
        }

        return $data;
    }
}
