<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Exception;

class UnableAccountCreate extends Logic
{
    private \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection;

    public function __construct(\M2E\Core\Model\Connector\Response\MessageCollection $messageCollection)
    {
        $this->messageCollection = $messageCollection;
        $message = implode(
            ', ',
            array_map(
                static fn(\M2E\Core\Model\Connector\Response\Message $message) => $message->getText(),
                $messageCollection->getMessages()
            )
        );
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->messageCollection->getErrors();
    }

    public function getMessageCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }
}
