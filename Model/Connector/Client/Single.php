<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Connector\Client;

class Single
{
    private \M2E\Core\Model\Connector\Client\Single $client;
    private \M2E\OnBuy\Model\Connector\Protocol $protocol;
    private Config $config;
    private \M2E\OnBuy\Helper\Module\Exception $exceptionLogger;
    private \M2E\Core\Model\Connector\Client\SingleFactory $coreClientFactory;
    /** @var \M2E\OnBuy\Model\Connector\Client\ModuleInfo */
    private ModuleInfo $moduleInfo;

    public function __construct(
        \M2E\Core\Model\Connector\Client\SingleFactory $coreClientFactory,
        Config $config,
        ModuleInfo $moduleInfo,
        \M2E\OnBuy\Model\Connector\Protocol $protocol,
        \M2E\OnBuy\Helper\Module\Exception $exceptionLogger
    ) {
        $this->protocol = $protocol;
        $this->config = $config;
        $this->exceptionLogger = $exceptionLogger;
        $this->coreClientFactory = $coreClientFactory;
        $this->moduleInfo = $moduleInfo;
    }

    /**
     * @param \M2E\Core\Model\Connector\CommandInterface $command
     *
     * @return object
     * @throws \M2E\Core\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function process(\M2E\Core\Model\Connector\CommandInterface $command): object
    {
        try {
            $commandResponseResult = $this->getClient()
                                          ->process($command);
        } catch (\Throwable $e) {
            $this->exceptionLogger->process($e, ['command' => $command->getCommand()]);

            throw $e;
        }

        return $commandResponseResult;
    }

    private function getClient(): \M2E\Core\Model\Connector\Client\Single
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->client)) {
            $this->client = $this->coreClientFactory->create(
                $this->protocol,
                $this->config,
                $this->moduleInfo
            );
        }

        return $this->client;
    }
}
