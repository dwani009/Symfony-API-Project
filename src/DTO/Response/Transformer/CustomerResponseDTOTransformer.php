<?php

declare(strict_types=1);

namespace App\DTO\Response\Transformer;

use App\DTO\Response\CustomerResponseDTO;
use App\Entity\Customer;

class CustomerResponseDTOTransformer extends AbstractResponseDTOTransformer
{
    /**
     * @param Customer $customer
     * @return CustomerResponseDTO
     */
    public function transformFromObject($customer): CustomerResponseDTO
    {
        $dto = new CustomerResponseDTO();

        $dto->id = $customer->getId();
        $dto->email = $customer->getEmail();
        $dto->phoneNumber = $customer->getPhoneNumber();

        return $dto;
    }
}