<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Description;

class ChangeProcessor extends \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_description_change_processor';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param \M2E\OnBuy\Model\Policy\Description\Diff $diff
     */
    protected function getInstructionsData(
        \M2E\OnBuy\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        /** @var \M2E\OnBuy\Model\Policy\Description\Diff $diff */

        $data = [];

        if ($diff->isTitleDifferent()) {
            $priority = 5;

            if ($status === \M2E\OnBuy\Model\Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isDescriptionDifferent()) {
            $priority = 5;

            if ($status === \M2E\OnBuy\Model\Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isImagesDifferent()) {
            $priority = 5;

            if ($status === \M2E\OnBuy\Model\Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        return $data;
    }
}
