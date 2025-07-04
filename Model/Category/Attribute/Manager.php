<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Attribute;

class Manager
{
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\OnBuy\Model\Category\Attribute\Repository $categoryAttributeRepository;
    private \Magento\Framework\App\ResourceConnection $resource;
    private \M2E\OnBuy\Model\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \M2E\OnBuy\Model\Template\Category\DiffFactory $diffFactory;
    private \M2E\OnBuy\Model\Template\Category\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\OnBuy\Model\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\OnBuy\Model\AttributeMapping\GeneralService $attributeMappingGeneralService;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\OnBuy\Model\Category\Attribute\Repository $categoryAttributeRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\OnBuy\Model\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory,
        \M2E\OnBuy\Model\Template\Category\DiffFactory $diffFactory,
        \M2E\OnBuy\Model\Template\Category\ChangeProcessorFactory $changeProcessorFactory,
        \M2E\OnBuy\Model\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\OnBuy\Model\AttributeMapping\GeneralService $attributeMappingGeneralService
    ) {
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->resource = $resource;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->attributeMappingGeneralService = $attributeMappingGeneralService;
    }

    /**
     * @param \M2E\OnBuy\Model\Category\CategoryAttribute[] $attributes
     * @param \M2E\OnBuy\Model\Category\Dictionary $dictionary
     *
     * @return void
     * @throws \Exception
     */
    public function createOrUpdateAttributes(
        array $attributes,
        \M2E\OnBuy\Model\Category\Dictionary $dictionary
    ): void {
        $attributesSortedById = [];
        $countOfUsedAttributes = 0;

        foreach ($attributes as $attribute) {
            $attributesSortedById[$attribute->getAttributeId()] = $attribute;
            if (
                !empty($attribute->getCustomValue())
                || !empty($attribute->getCustomAttributeValue())
                || !empty($attribute->getRecommendedValue())
            ) {
                $countOfUsedAttributes++;
            }
        }

        $transaction = $this->resource->getConnection()->beginTransaction();
        try {
            $oldSnapshot = $this->getSnapshot($dictionary);

            $existedAttributes = $this->categoryAttributeRepository
                ->findByDictionaryId($dictionary->getId());

            foreach ($existedAttributes as $existedAttribute) {
                $inputAttribute = $attributesSortedById[$existedAttribute->getAttributeId()] ?? null;
                if ($inputAttribute === null) {
                    continue;
                }

                $this->updateAttribute($existedAttribute, $inputAttribute);
                unset($attributesSortedById[$existedAttribute->getAttributeId()]);
            }

            foreach ($attributesSortedById as $attribute) {
                $this->createAttribute($attribute);
            }

            $newSnapshot = $this->getSnapshot($dictionary);

            $this->addInstruction($dictionary, $oldSnapshot, $newSnapshot);

            $dictionary->setUsedProductAttributes($countOfUsedAttributes);
            $dictionary->installStateSaved();
            $this->categoryDictionaryRepository->save($dictionary);

            $this->attributeMappingGeneralService->create($dictionary->getRelatedAttributes());
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        $transaction->commit();
    }

    private function updateAttribute(
        \M2E\OnBuy\Model\Category\CategoryAttribute $existedAttribute,
        \M2E\OnBuy\Model\Category\CategoryAttribute $inputAttribute
    ) {
        $existedAttribute->setCategoryDictionaryId($inputAttribute->getCategoryDictionaryId());
        $existedAttribute->setAttributeType($inputAttribute->getAttributeType());
        $existedAttribute->setAttributeId($inputAttribute->getAttributeId());
        $existedAttribute->setAttributeName($inputAttribute->getAttributeName());
        $existedAttribute->setValueMode($inputAttribute->getValueMode());
        $existedAttribute->setRecommendedValue($inputAttribute->getRecommendedValue());
        $existedAttribute->setCustomValue($inputAttribute->getCustomValue());
        $existedAttribute->setCustomAttributeValue($inputAttribute->getCustomAttributeValue());

        $this->categoryAttributeRepository->save($existedAttribute);
    }

    private function createAttribute(\M2E\OnBuy\Model\Category\CategoryAttribute $attribute)
    {
        $this->categoryAttributeRepository->create($attribute);
    }

    private function getSnapshot(\M2E\OnBuy\Model\Category\Dictionary $dictionary): array
    {
        return $this->snapshotBuilderFactory
            ->create()
            ->setModel($dictionary)
            ->getSnapshot();
    }

    private function addInstruction(
        \M2E\OnBuy\Model\Category\Dictionary $dictionary,
        array $oldSnapshot,
        array $newSnapshot
    ): void {
        $diff = $this->diffFactory->create();
        $diff->setOldSnapshot($oldSnapshot);
        $diff->setNewSnapshot($newSnapshot);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($dictionary);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }
}
