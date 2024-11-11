<?php
namespace App\DTO\Response\Transformer;

use App\DTO\Response\CartResponseDTO;
use App\Entity\Cart;

class CartResponseDTOTransformer extends AbstractResponseDTOTransformer
{
    private CustomerResponseDTOTransformer $customerResponseDTOTransformer;
    private ProductResponseDTOTransformer $productResponseDTOTransformer;

    /**
     * @param CustomerResponseDTOTransformer $customerResponseDTOTransformer
     * @param ProductResponseDTOTransformer $productResponseDTOTransformer
     */
    public function __construct(
        CustomerResponseDTOTransformer $customerResponseDTOTransformer,
        ProductResponseDTOTransformer  $productResponseDTOTransformer
    ) {
        $this->customerResponseDTOTransformer = $customerResponseDTOTransformer;
        $this->productResponseDTOTransformer = $productResponseDTOTransformer;
    }

    /**
     * @param Cart $cart
     * @return CartResponseDTO
     */
    public function transformFromObject($cart): CartResponseDTO
    {
        $dto = new CartResponseDTO();
        $dto->cartId = $cart->getId();
        $dto->createdAt = $cart->getDateTime();
        $dto->customer = $this->customerResponseDTOTransformer->transformFromObject($cart->getCustomer());
        $dto->products = $this->productResponseDTOTransformer->transformFromObjects($cart->getProducts()->toArray());

        return $dto;
    }

    /**
     * Transform Cart to array for caching.
     *
     * @param Cart $cart
     * @return array
     */
    public function transformToArray(Cart $cart): array
    {
        $dto = $this->transformFromObject($cart);
        return [
            'cartId' => $dto->cartId,
            'createdAt' => $dto->createdAt->format('Y-m-d H:i:s'),
            'customer' => [
                'id' => $dto->customer->id,
                'email' => $dto->customer->email,
                'phoneNumber' => $dto->customer->phoneNumber
            ],
            'products' => array_map(function($product) {
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'title' => $product->title,
                    'price' => $product->price,
                ];
            }, $dto->products)
        ];
    }
}