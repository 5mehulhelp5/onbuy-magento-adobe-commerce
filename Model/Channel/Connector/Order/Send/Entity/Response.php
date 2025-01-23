<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity;

class Response
{
    private bool $isSuccess;
    private array $errorMessages;
    private array $warningMessages;

    public function __construct(
        bool $isSuccess,
        array $errorMessages = [],
        array $warningMessages = []
    ) {
        $this->isSuccess = $isSuccess;
        $this->errorMessages = $errorMessages;
        $this->warningMessages = $warningMessages;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function hasWarnings(): bool
    {
        return count($this->warningMessages) > 0;
    }

    /**
     * @return \M2E\Core\Model\Response\Message[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @return \M2E\Core\Model\Response\Message[]
     */
    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }
}
