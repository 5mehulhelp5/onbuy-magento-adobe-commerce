<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Shipping;

class SaveService
{
    private \M2E\OnBuy\Model\Policy\ShippingFactory $shippingFactory;
    private \M2E\OnBuy\Model\Policy\Shipping\Repository $shippingRepository;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Policy\Shipping\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \M2E\OnBuy\Model\Policy\Shipping\DiffFactory $diffFactory;
    private \M2E\OnBuy\Model\Policy\Shipping\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\OnBuy\Model\Policy\Shipping\ChangeProcessorFactory $changeProcessorFactory;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Policy\ShippingFactory $shippingFactory,
        \M2E\OnBuy\Model\Policy\Shipping\Repository $shippingRepository,
        \M2E\OnBuy\Model\Policy\Shipping\SnapshotBuilderFactory $snapshotBuilderFactory,
        \M2E\OnBuy\Model\Policy\Shipping\DiffFactory $diffFactory,
        \M2E\OnBuy\Model\Policy\Shipping\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\OnBuy\Model\Policy\Shipping\ChangeProcessorFactory $changeProcessorFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->shippingFactory = $shippingFactory;
        $this->shippingRepository = $shippingRepository;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
    }

    public function save(array $data): \M2E\OnBuy\Model\Policy\Shipping
    {
        if (empty($data['id'])) {
            $oldData = [];
            $shipping = $this->create($data);
        } else {
            $templateModel = $this->shippingRepository->get((int)$data['id']);
            $oldData = $this->makeSnapshot($templateModel);

            $shipping = $this->update($data);
        }

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($shipping);

        $newData = $this->makeSnapshot($shipping);

        $diff = $this->diffFactory->create();

        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($shipping);

        $changeProcessor = $this->changeProcessorFactory->create();

        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );

        return $shipping;
    }

    private function create(array $data): \M2E\OnBuy\Model\Policy\Shipping
    {
        $account = $this->accountRepository->get((int)$data['account_id']);

        $shipping = $this->shippingFactory->create(
            $account,
            (int)$data['site_id'],
            $data['title'],
            (int)$data['delivery_template_id'],
            (int)$data['handling_time_mode'],
            (int)$data['handling_time'],
            (string)$data['handling_time_attribute']
        );
        $this->shippingRepository->create($shipping);

        return $shipping;
    }

    private function update(array $data): \M2E\OnBuy\Model\Policy\Shipping
    {
        $shipping = $this->shippingRepository->get((int)$data['id']);

        $shipping->setTitle($data['title'])
                 ->setDeliveryTemplateId((int)$data['delivery_template_id'])
                 ->setHandlingTimeMode((int)$data['handling_time_mode'])
                 ->setHandlingTime((int)$data['handling_time'])
                 ->setHandlingTimeAttribute((string)$data['handling_time_attribute']);

        $this->shippingRepository->save($shipping);

        return $shipping;
    }

    private function makeSnapshot($model)
    {
        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($model);

        return $snapshotBuilder->getSnapshot();
    }
}
