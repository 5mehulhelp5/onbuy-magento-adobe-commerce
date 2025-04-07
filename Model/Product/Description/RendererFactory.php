<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Description;

class RendererFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\OnBuy\Model\Product $listingProduct): Renderer
    {
        return $this->objectManager->create(Renderer::class, [
            'listingProduct' => $listingProduct,
        ]);
    }
}
