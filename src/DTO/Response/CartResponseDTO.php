<?php

declare(strict_types=1);

namespace App\DTO\Response;

use DateTime;
use JMS\Serializer\Annotation as Serialization;

class CartResponseDTO
{
    /**
     * @var int
     * @Serialization\Type("int")
     * @Serialization\Groups({"admin"})
     */
    public int $cartId;

    /**
     * @var DateTime
     * @Serialization\Type("DateTime<'Y-m-d h:i:s'>"))
     * @Serialization\Groups({"admin"})
     */
    public DateTime $createdAt;

    /**
     * @var CustomerResponseDTO
     * @Serialization\Type("App\DTO\Response\CustomerResponseDTO"))
     * @Serialization\Groups({"admin", "user"})
     */
    public CustomerResponseDTO $customer;

    /**
     * @var array
     * @Serialization\Type("array<App\DTO\Response\ProductResponseDTO>"))
     * @Serialization\Groups({"admin", "user"})
     */
    public array $products;
}