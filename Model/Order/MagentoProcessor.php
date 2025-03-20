<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class MagentoProcessor
{
    /** @var \M2E\OnBuy\Model\Order\MagentoProcessor\InvoiceCreate */
    private MagentoProcessor\InvoiceCreate $magentoInvoiceCreate;
    /** @var \M2E\OnBuy\Model\Order\MagentoProcessor\ShipmentCreate */
    private MagentoProcessor\ShipmentCreate $magentoShipmentCreate;
    /** @var \M2E\OnBuy\Model\Order\MagentoProcessor\ShipmentTrackCreate */
    private MagentoProcessor\ShipmentTrackCreate $magentoTrackCreate;
    /** @var \M2E\OnBuy\Model\Order\MagentoProcessor\HandleOrder */
    private MagentoProcessor\HandleOrder $handleOrder;
    /** @var \M2E\OnBuy\Model\Order\MagentoProcessor\Check\ParallelProcess */
    private MagentoProcessor\Check\ParallelProcess $checkParallelProcess;
    private MagentoProcessor\CreditMemoCreate $magentoCreditMemoCreate;
    private \M2E\OnBuy\Model\Synchronization\LogService $syncLogService;
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        MagentoProcessor\HandleOrder $orderCreate,
        MagentoProcessor\InvoiceCreate $magentoInvoiceCreate,
        MagentoProcessor\ShipmentCreate $magentoShipmentCreate,
        MagentoProcessor\ShipmentTrackCreate $magentoTrackCreate,
        MagentoProcessor\CreditMemoCreate $magentoCreditMemoCreate,
        \M2E\OnBuy\Model\Order\MagentoProcessor\Check\ParallelProcess $checkParallelProcess,
        \M2E\OnBuy\Model\Synchronization\LogService $syncLogService,
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper
    ) {
        $this->magentoCreditMemoCreate = $magentoCreditMemoCreate;
        $this->magentoInvoiceCreate = $magentoInvoiceCreate;
        $this->magentoShipmentCreate = $magentoShipmentCreate;
        $this->magentoTrackCreate = $magentoTrackCreate;
        $this->handleOrder = $orderCreate;
        $this->checkParallelProcess = $checkParallelProcess;
        $this->syncLogService = $syncLogService;
        $this->exceptionHelper = $exceptionHelper;
    }

    /**
     * @param \M2E\OnBuy\Model\Order $order
     * @param bool $isForce
     * @param int $initiator
     * @param bool $processReserve
     * @param bool $addLogAboutCreate
     *
     * @return void
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \M2E\OnBuy\Model\Order\Exception\UnableCreateMagentoOrder
     */
    public function process(
        \M2E\OnBuy\Model\Order $order,
        int $initiator,
        bool $processReserve,
        bool $addLogAboutCreate
    ): void {
        $this->handleOrder->process($order, $initiator, $addLogAboutCreate);

        if ($processReserve) {
            if (
                $order->getReserve()->isNotProcessed()
                && $order->isReservable()
            ) {
                $order->getReserve()->place();
            }
        }

        $this->magentoInvoiceCreate->process($order);
        $this->magentoShipmentCreate->process($order);
        $this->magentoTrackCreate->process($order);
        $this->magentoCreditMemoCreate->process($order);
    }

    /**
     * @param \M2E\OnBuy\Model\Order[] $orders
     * @param int $initiator
     * @param bool $processReserve
     * @param bool $addLogAboutCreate
     *
     * @return void
     */
    public function processBatch(
        array $orders,
        int $initiator,
        bool $processReserve,
        bool $addLogAboutCreate
    ): void {
        foreach ($orders as $order) {
            if ($this->checkParallelProcess->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            try {
                $this->process($order, $initiator, $processReserve, $addLogAboutCreate);
            } catch (\M2E\OnBuy\Model\Order\Exception\UnableCreateMagentoOrder $e) {
                continue;
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);

                continue;
            }
        }
    }
}
