<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Images;

class Value
{
    public string $mainImage;

    /** @var \M2E\OnBuy\Model\Product\DataProvider\Images\Image[] */
    public array $galleryImages;

    public function __construct(
        string $mainImage,
        array $galleryImages
    ) {
        $this->mainImage = $mainImage;
        $this->galleryImages = $galleryImages;
    }
}
