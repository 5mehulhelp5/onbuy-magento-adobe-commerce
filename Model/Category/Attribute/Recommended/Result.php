<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Attribute\Recommended;

class Result
{
    private const STATUS_FAIL = 'fail';
    private const STATUS_SUCCESS = 'success';

    private string $status;
    private string $errorMessage;
    private ?int $id;

    private function __construct(
        string $status,
        string $errorMessage,
        ?int $id
    ) {
        $this->status = $status;
        $this->errorMessage = $errorMessage;
        $this->id = $id;
    }

    public static function createFail(string $errorMessage): self
    {
        return new self(self::STATUS_FAIL, $errorMessage, null);
    }

    public static function createSuccess(int $id): self
    {
        return new self(self::STATUS_SUCCESS, '', $id);
    }

    // ----------------------------------------

    public function getFailMessages(): string
    {
        return $this->errorMessage;
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }

    public function getResult(): ?int
    {
        return $this->id;
    }
}
