<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Stop;

use M2E\OnBuy\Model\Product\Action\Type\AbstractValidator;

class ProcessStart extends \M2E\OnBuy\Model\Product\Action\Async\AbstractProcessStart
{
    private \M2E\OnBuy\Model\Product\Action\Type\Stop\Request $request;
    private RequestFactory $requestFactory;
    private \M2E\OnBuy\Model\Product\Action\Type\AbstractValidatorFactory $actionValidatorFactory;
    private \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator $actionValidator;

    public function __construct(
        RequestFactory $requestFactory,
        ValidatorFactory $actionValidatorFactory,
        \M2E\OnBuy\Model\Product\Action\TagManager $tagManager
    ) {
        parent::__construct($tagManager);

        $this->requestFactory = $requestFactory;
        $this->actionValidatorFactory = $actionValidatorFactory;
    }

    protected function getActionNick(): string
    {
        return \M2E\OnBuy\Model\Product\Action\Async\DefinitionsCollection::ACTION_STOP;
    }

    protected function getActionValidator(): AbstractValidator
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->actionValidator)) {
            return $this->actionValidator;
        }

        return $this->actionValidator = $this->actionValidatorFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getParams()
        );
    }

    protected function getCommand(): \M2E\Core\Model\Connector\CommandProcessingInterface
    {
        $requestData = $this->getRequest()->build(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams()
        );

        return new \M2E\OnBuy\Model\Channel\Connector\Product\StopCommand(
            $this->getAccount()->getServerHash(),
            $this->getListingProduct()->getListing()->getSite()->getSiteId(),
            $requestData->getData()
        );
    }

    protected function getRequestMetadata(): array
    {
        return $this->getRequest()->getMetadata();
    }

    private function getRequest(): \M2E\OnBuy\Model\Product\Action\Type\Stop\Request
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->request)) {
            return $this->request;
        }

        return $this->request = $this->requestFactory->create();
    }
}
