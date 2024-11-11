<?php

declare(strict_types=1);

namespace App\DTO\Response\Transformer;

use App\DTO\Response\ProductResponseDTO;
use App\Entity\Product;

class ProductResponseDTOTransformer extends AbstractResponseDTOTransformer
{
    /**
     * @param Product $product
     * @return ProductResponseDTO
     */
    public function transformFromObject($product): ProductResponseDTO
    {
        $dto = new ProductResponseDTO();

        $dto->id = $product->getId();
        $dto->code = $product->getCode();
        $dto->title = $product->getTitle();
        $dto->price = $product->getPrice();

        return $dto;
    }
}