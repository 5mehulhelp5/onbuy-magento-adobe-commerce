<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Account\Update;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    private const SERVER_CHANGE_MODE_ERROR_CODE = 1400;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    /**
     * @param \M2E\OnBuy\Model\Account $account
     * @param string $consumerKey
     * @param string $secretKey
     *
     * @return \M2E\OnBuy\Model\Channel\Account
     * @throws \M2E\Core\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\OnBuy\Model\Exception\UnableAccountUpdate
     */
    public function process(
        \M2E\OnBuy\Model\Account $account,
        string $consumerKey,
        string $secretKey
    ): \M2E\OnBuy\Model\Channel\Account {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Account\UpdateCommand(
            $account->getServerHash(),
            $consumerKey,
            $secretKey
        );

        try {
            /** @var \M2E\OnBuy\Model\Channel\Account */
            return $this->serverClient->process($command);
        } catch (\M2E\Core\Model\Exception\Connection\SystemError $e) {
            $response = $e->getResponse();
            foreach ($response->getMessageCollection()->getMessages() as $message) {
                if ($message->getCode() === self::SERVER_CHANGE_MODE_ERROR_CODE) {
                    throw new \M2E\Core\Model\Exception\Connection\SystemError(
                        (string)__(
                            'Credentials for your LIVE seller account cannot be used to access the TEST mode.
                            Please make sure you are entering the right credentials.'
                        ),
                        $response
                    );
                }
            }

            throw $e;
        }
    }
}
