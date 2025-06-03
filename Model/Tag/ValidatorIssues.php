<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Tag;

class ValidatorIssues
{
    public const NOT_USER_ERROR = 'not-user-error';

    public const ERROR_BRAND_INVALID_OR_MISSING = '0001-m2e';
    public const ERROR_CATEGORY_SETTINGS_NOT_SET = '0002-m2e';
    public const ERROR_CONDITION_NOT_SPECIFIED = '0003-m2e';
    public const ERROR_DESCRIPTION_MISSING = '0004-m2e';
    public const ERROR_EAN_MISSING = '0005-m2e';
    public const ERROR_MAIN_IMAGE_MISSING = '0006-m2e';
    public const ERROR_IMAGES_INVALID = '0007-m2e';
    public const ERROR_ZERO_PRICE = '0008-m2e';
    public const ERROR_ZERO_QTY = '0009-m2e';
    public const ERROR_DUPLICATE_OPC_UNMANAGED = '0010-m2e';
    public const ERROR_DUPLICATE_OPC_LISTING = '0011-m2e';
    public const ERROR_DUPLICATE_SKU_UNMANAGED = '0012-m2e';
    public const ERROR_DUPLICATE_SKU_LISTING = '0013-m2e';
    public const ERROR_INVALID_PRODUCT_NAME_LENGTH = '0014-m2e';

    public function mapByCode(string $code): ?\M2E\OnBuy\Model\Product\Action\Validator\ValidatorMessage
    {
        $map = [
            self::ERROR_BRAND_INVALID_OR_MISSING => (string)__('Brand is not valid or missing a value.'),
            self::ERROR_CATEGORY_SETTINGS_NOT_SET => (string)__('Category Settings are not set.'),
            self::ERROR_CONDITION_NOT_SPECIFIED => (string)__('The Product Сondition is not specified.'),
            self::ERROR_DESCRIPTION_MISSING => (string)__('Product Description is missing.'),
            self::ERROR_EAN_MISSING => (string)__('EAN is missing a value.'),
            self::ERROR_MAIN_IMAGE_MISSING => (string)__('Product Image is missing.'),
            self::ERROR_IMAGES_INVALID => (string)__('Product Images are invalid.'),
            self::ERROR_ZERO_PRICE => (string)__('The Product Price must be greater than 0.'),
            self::ERROR_ZERO_QTY => (string)__('The Product Quantity must be greater than 0.'),
            self::ERROR_DUPLICATE_OPC_UNMANAGED => (string)__('Product with the same OPC and Condition already exists in Unmanaged Items.'),
            self::ERROR_DUPLICATE_OPC_LISTING => (string)__('Product with the same OPC and condition already exists in another Listing.'),
            self::ERROR_DUPLICATE_SKU_UNMANAGED => (string)__('Product with the same SKU already exists in Unmanaged Items.'),
            self::ERROR_DUPLICATE_SKU_LISTING => (string)__('Product with the same SKU already exists in another Listing.'),
            self::ERROR_INVALID_PRODUCT_NAME_LENGTH => (string)__('The product name must contain between 1 and 150 characters.'),
        ];

        if (!isset($map[$code])) {
            return null;
        }

        return new \M2E\OnBuy\Model\Product\Action\Validator\ValidatorMessage(
            $map[$code],
            $code
        );
    }
}
