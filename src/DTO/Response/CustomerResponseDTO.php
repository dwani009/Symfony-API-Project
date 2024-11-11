<?php

declare(strict_types=1);

namespace App\DTO\Response;

use JMS\Serializer\Annotation as Serialization;

class CustomerResponseDTO
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
     * @Serialization\Groups({"admin", "user"})
     */
    public string $email;

    /**
     * @var string
     * @Serialization\Type("string"))
     * @Serialization\Groups({"admin", "user"})
     */
    public string $phoneNumber;
}