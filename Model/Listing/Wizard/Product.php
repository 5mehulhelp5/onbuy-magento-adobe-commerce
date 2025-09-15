<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing\Wizard;

use M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Product as WizardProductResource;

class Product extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    public const SEARCH_STATUS_NONE = 0;
    public const SEARCH_STATUS_COMPLETED = 1;

    protected ?\M2E\OnBuy\Model\Magento\Product\Cache $magentoProductModel = null;

    private \M2E\OnBuy\Model\Listing\Wizard $wizard;

    /** @var \M2E\OnBuy\Model\Listing\Wizard\Repository */
    private Repository $repository;
    private \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        Repository $repository,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->repository = $repository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->dictionaryRepository = $dictionaryRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(WizardProductResource::class);
    }

    public function init(\M2E\OnBuy\Model\Listing\Wizard $wizard, int $magentoProductId): self
    {
        $this
            ->setData(WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId())
            ->setData(WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function initWizard(\M2E\OnBuy\Model\Listing\Wizard $wizard): self
    {
        $this->wizard = $wizard;

        return $this;
    }

    public function getWizard(): \M2E\OnBuy\Model\Listing\Wizard
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->wizard)) {
            $this->wizard = $this->repository->get($this->getWizardId());
        }

        return $this->wizard;
    }

    public function getWizardId(): int
    {
        return (int)$this->getData(WizardProductResource::COLUMN_WIZARD_ID);
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function getUnmanagedProductId(): ?int
    {
        $value = $this->getData(WizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID);

        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public function setUnmanagedProductId(int $value): self
    {
        $this->setData(WizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID, $value);

        return $this;
    }

    public function getCategoryDictionary(): ?\M2E\OnBuy\Model\Category\Dictionary
    {
        $dictionaryId = $this->getCategoryDictionaryId();
        if ($dictionaryId === null) {
            return null;
        }

        return $this->dictionaryRepository->get($dictionaryId);
    }

    public function getCategoryId(): ?string
    {
        $dictionary = $this->getCategoryDictionary();
        if ($dictionary === null) {
            return null;
        }

        return $dictionary->getCategoryId();
    }

    public function setCategoryId(int $value): self
    {
        $this->setData(WizardProductResource::COLUMN_CATEGORY_ID, $value);

        return $this;
    }

    public function getCategoryDictionaryId(): ?int
    {
        $value = $this->getData(WizardProductResource::COLUMN_CATEGORY_ID);
        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public function isProcessed(): bool
    {
        return (bool)$this->getData(WizardProductResource::COLUMN_IS_PROCESSED);
    }

    public function processed(): self
    {
        $this->setData(WizardProductResource::COLUMN_IS_PROCESSED, 1);

        return $this;
    }

    public function getChannelProductId(): string
    {
        return (string)$this->getData(WizardProductResource::COLUMN_CHANNEL_PRODUCT_ID);
    }

    public function setChannelProductId(string $channelProductId): self
    {
        $this->setData(WizardProductResource::COLUMN_CHANNEL_PRODUCT_ID, $channelProductId);
        $this->markChannelIdIsSearched();

        return $this;
    }

    public function markChannelIdIsSearched(): self
    {
        $this->setData(WizardProductResource::COLUMN_CHANNEL_PRODUCT_ID_SEARCH_STATUS, self::SEARCH_STATUS_COMPLETED);

        return $this;
    }

    public function setChannelProductData(array $data): self
    {
        $this->setData(WizardProductResource::COLUMN_CHANNEL_PRODUCT_DATA, json_encode($data));

        return $this;
    }

    public function getChannelProductData(): array
    {
        $data = $this->getData(WizardProductResource::COLUMN_CHANNEL_PRODUCT_DATA);

        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function getMagentoProduct(): \M2E\OnBuy\Model\Magento\Product\Cache
    {
        if ($this->magentoProductModel === null) {
            $this->magentoProductModel = $this->magentoProductFactory->create();
            $this->magentoProductModel->setProductId($this->getMagentoProductId());
        }

        return $this->prepareMagentoProduct($this->magentoProductModel);
    }

    protected function prepareMagentoProduct(
        \M2E\OnBuy\Model\Magento\Product\Cache $instance
    ): \M2E\OnBuy\Model\Magento\Product\Cache {
        $instance->setStoreId($this->getWizard()->getListing()->getStoreId());
        $instance->setStatisticId($this->getId());

        return $instance;
    }

    public function markCategoryAttributesAsValid(): void
    {
        $this->setCategoryAttributesValid(true);
        $this->setCategoryAttributesErrors([]);
    }

    /**
     * @param string[] $errors
     *
     * @return void
     */
    public function markCategoryAttributesAsInvalid(array $errors): void
    {
        $this->setCategoryAttributesValid(false);
        $this->setCategoryAttributesErrors($errors);
    }

    public function isInvalidCategoryAttributes(): bool
    {
        $value = $this->getData(WizardProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES);

        return $value === null ? false : !$value;
    }

    private function setCategoryAttributesValid(bool $isValid): void
    {
        $this->setData(WizardProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES, $isValid);
    }

    private function setCategoryAttributesErrors(array $errors): void
    {
        $value = null;
        if (!empty($errors)) {
            $value = json_encode($errors);
        }

        $this->setData(WizardProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS, $value);
    }

    /**
     * @return string[]
     */
    public function getCategoryAttributesErrors(): array
    {
        $value = $this->getData(WizardProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }
}
