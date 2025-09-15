<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Category\Attribute;

class ValidateManager
{
    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\Product\Action\TagManager $tagManager;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\Product\Action\TagManager $tagManager
    ) {
        $this->productRepository = $productRepository;
        $this->tagManager = $tagManager;
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param string[] $errors
     *
     * @return void
     */
    public function markProductAsNotValid(\M2E\OnBuy\Model\Product $product, array $errors): void
    {
        $product->markCategoryAttributesAsInvalid($errors);
        $this->productRepository->save($product);

        $messages[] = new \M2E\OnBuy\Model\Product\Action\Validator\ValidatorMessage(
            'The Item either is Listed, or not Listed yet or not available',
            \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_CATEGORY_ATTRIBUTE_MISSING
        );
        $this->tagManager->addErrorTags($product, $messages);
        $this->tagManager->flush();
    }

    public function markProductAsValid(\M2E\OnBuy\Model\Product $product): void
    {
        $product->markCategoryAttributesAsValid();
        $this->productRepository->save($product);

        $this->tagManager->removeTagByCode(
            $product,
            \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_CATEGORY_ATTRIBUTE_MISSING
        );
        $this->tagManager->flush();
    }
}
