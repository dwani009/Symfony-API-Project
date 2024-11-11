<?php

declare(strict_types=1);

namespace App\DTO\Response;

use JMS\Serializer\Annotation as Serialization;
class ProductResponseDTO
{
    /**
     * @var int
     * @Serialization\Type("integer"))
     * @Serialization\Groups({"admin"})
     */
    public int $id;

    /**
     * @var string
     * @Serialization\Type("string"))
     * @Serialization\Groups({"admin"})
     */
    public string $code;

    /**
     * @var string
     * @Serialization\Type("string"))
     * @Serialization\Groups({"admin", "user"})
     */
    public string $title;

    /**
     * @var int
     * @Serialization\Type("integer"))
     * @Serialization\Groups({"admin", "user"})
     */
    public int $price;
}