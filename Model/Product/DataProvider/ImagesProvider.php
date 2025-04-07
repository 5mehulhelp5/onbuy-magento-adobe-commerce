<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class ImagesProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Images';

    private string $images = '';

    private string $mainImage = '';

    private string $galleryImages = '';

    public function getImages(\M2E\OnBuy\Model\Product $product): Images\Value
    {
        $mainImage = $this->getMainImage($product);
        $galleryImages = $this->getGalleryImages($product);

        return new Images\Value($mainImage, $galleryImages);
    }

    private function getGalleryImages(\M2E\OnBuy\Model\Product $product): array
    {
        $productImageSet = $product->getDescriptionTemplateSource()->getGalleryImages();

        $result = [];

        foreach ($productImageSet as $productImage) {
            $result[] = $productImage->getUrl();
        }

        $data = json_encode($result);
        $this->galleryImages = \M2E\Core\Helper\Data::md5String($data);

        return $result;
    }

    private function getMainImage(\M2E\OnBuy\Model\Product $product): string
    {
        $imageUrl = '';
        $mainImage = $product->getDescriptionTemplateSource()->getMainImage();

        if ($mainImage !== null) {
            $imageUrl = $mainImage->getUrl();
            $this->mainImage = $mainImage->getHash();
        }

        return $imageUrl;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'gallery_images' => $this->galleryImages,
                'main_image' => $this->mainImage,
            ],
        ];
    }
}
