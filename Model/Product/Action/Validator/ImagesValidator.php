<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class ImagesValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        if (!$configurator->isImagesAllowed()) {
            return null;
        }

        $image = $product->getDataProvider()->getImages()->getValue()->mainImage;
        $images = $product->getDataProvider()->getImages()->getValue()->galleryImages;

        if ($image === '') {
            return (string)__(
                'Product Image is missing. To list the Product, ' .
                'please make sure that the Image settings in the Description policy are correct and the Images ' .
                'are available in the Magento Product.'
            );
        }

        foreach ($images as $image) {
            if (!$this->isValidUrl($image->url)) {
                return (string)__(
                    'Product Images are invalid. To list the Product, ' .
                    'please make sure that the Image settings in the Description policy are correct and the Images ' .
                    'are available in the Magento Product.'
                );
            }
        }

        return null;
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
