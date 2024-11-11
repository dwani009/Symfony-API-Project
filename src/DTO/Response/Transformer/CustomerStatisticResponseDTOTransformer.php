<?php

declare(strict_types=1);

namespace App\DTO\Response\Transformer;

use App\DTO\Response\CustomerStatisticResponseDTO;
use App\Entity\Customer;
use App\Repository\CartRepository;

class CustomerStatisticResponseDTOTransformer extends AbstractResponseDTOTransformer
{

    /**
     * @var CartRepository
     */
    private $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param Customer $customer
     *
     * @return CustomerStatisticResponseDTO
     */
    public function transformFromObject($customer): CustomerStatisticResponseDTO
    {
        $dto = new CustomerStatisticResponseDTO();
        $dto->customerId = $customer->getId();
        $dto->cartTotalCount = $this->cartRepository->getTotalCountByCustomer($customer);

        $dto->setCartTotalPrice(function() use ($customer) {
            return $this->cartRepository->getTotalPriceByCustomer($customer);
        });

        return $dto;
    }

    /**
     * @param array $data
     * @return CustomerStatisticResponseDTO
     */
    public function transformFromArray(array $data): CustomerStatisticResponseDTO
    {
        $dto = new CustomerStatisticResponseDTO();
        $dto->customerId = $data['customerId'];
        $dto->cartTotalCount = $data['cartTotalCount'];

        $dto->setCartTotalPrice(fn() => $data['cartTotalPrice'] ?? 0);

        return $dto;
    }
}