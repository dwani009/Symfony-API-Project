<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Closure;
use JMS\Serializer\Annotation as Serialization;

class CustomerStatisticResponseDTO
{
    /**
     * @var int
     * @Serialization\Type("int")
     * @Serialization\Groups({"admin"})
     */
    public int $customerId;

    /**
     * @var int
     * @Serialization\Type("integer")
     * @Serialization\Groups({"admin"})
     */
    public int $cartTotalCount;

    /**
     * @var Closure
     * @Serialization\Type("integer")
     * @Serialization\Accessor(getter="getCartTotalPrice")
     * @Serialization\Groups({"admin"})
     */
    private Closure $cartTotalPrice;

    /**
     * Virtual property for completed carts
     *
     * @Serialization\VirtualProperty
     * @Serialization\SerializedName("completed_carts")
     * @Serialization\Groups({"admin"})
     */
    public function getCompletedCarts(): int
    {
        return rand(1, 10);
    }

    /**
     * @return int|null
     */
    public function getCartTotalPrice(): ?int
    {
        $callable = $this->cartTotalPrice;
        return $callable();
    }

    /**
     * @param Closure $cartTotalPrice
     */
    public function setCartTotalPrice(Closure $cartTotalPrice): void
    {
        $this->cartTotalPrice = $cartTotalPrice;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'customerId' => $this->customerId,
            'cartTotalCount' => $this->cartTotalCount,
            'cartTotalPrice' => $this->getCartTotalPrice(),
            'completedCarts' => $this->getCompletedCarts(),
        ];
    }
}