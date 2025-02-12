<?php

namespace M2E\OnBuy\Model\ControlPanel\Inspection;

class Processor
{
    /** @var \M2E\OnBuy\Model\ControlPanel\Inspection\HandlerFactory */
    private $handlerFactory;

    /** @var \M2E\OnBuy\Model\ControlPanel\Inspection\Result\Factory */
    private $resultFactory;

    public function __construct(
        \M2E\OnBuy\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory,
        \M2E\OnBuy\Model\ControlPanel\Inspection\Result\Factory $resultFactory
    ) {
        $this->handlerFactory = $handlerFactory;
        $this->resultFactory = $resultFactory;
    }

    public function process(\M2E\OnBuy\Model\ControlPanel\Inspection\Definition $definition)
    {
        $handler = $this->handlerFactory->create($definition);

        try {
            $issues = $handler->process();
            $result = $this->resultFactory->createSuccess($issues);
        } catch (\Exception $e) {
            $result = $this->resultFactory->createFailed($e->getMessage());
        }

        return $result;
    }
}
