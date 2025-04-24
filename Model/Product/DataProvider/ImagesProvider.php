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

    /**
     * @param \M2E\OnBuy\Model\Product $product
     *
     * @return \M2E\Temu\Model\Product\DataProvider\Images\Image[]
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    private function getGalleryImages(\M2E\OnBuy\Model\Product $product): array
    {
        $productImageSet = $product->getDescriptionTemplateSource()->getGalleryImages();

        $result = [];

        foreach ($productImageSet as $productImage) {
            $result[] = new \M2E\OnBuy\Model\Product\DataProvider\Images\Image($productImage->getUrl());
        }

        $this->galleryImages = $this->generateImagesHash($result);

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

    /**
     * @param \M2E\Temu\Model\Product\DataProvider\Images\Image[] $images
     *
     * @return string
     */
    private function generateImagesHash(array $images): string
    {
        $flatImages = [];
        foreach ($images as $image) {
            $flatImages[] = $image->url;
        }

        sort($flatImages);

        return \M2E\Core\Helper\Data::md5String(json_encode($flatImages));
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
