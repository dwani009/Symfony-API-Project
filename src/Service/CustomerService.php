<?php

namespace App\Service;

use App\DTO\Response\CustomerResponseDTO;
use App\DTO\Response\CustomerStatisticResponseDTO;
use App\DTO\Response\Transformer\CustomerResponseDTOTransformer;
use App\DTO\Response\Transformer\CustomerStatisticResponseDTOTransformer;
use App\Entity\Customer;
use App\Message\CustomerNotificationMessage;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CustomerService
{
    private EntityManagerInterface $entityManager;
    private CustomerRepository $customerRepository;
    private CustomerResponseDTOTransformer $customerResponseTransformer;
    private CustomerStatisticResponseDTOTransformer $customerStatisticResponseTransformer;
    private RedisCacheService $redisCacheService;
    private MessageBusInterface $messageBus;

    /**
     * @param EntityManagerInterface $entityManager
     * @param CustomerRepository $customerRepository
     * @param CustomerResponseDTOTransformer $customerResponseTransformer
     * @param CustomerStatisticResponseDTOTransformer $customerStatisticResponseTransformer
     * @param RedisCacheService $redisCacheService
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerRepository $customerRepository,
        CustomerResponseDTOTransformer $customerResponseTransformer,
        customerStatisticResponseDTOTransformer $customerStatisticResponseTransformer,
        RedisCacheService $redisCacheService,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->customerResponseTransformer = $customerResponseTransformer;
        $this->customerStatisticResponseTransformer = $customerStatisticResponseTransformer;
        $this->redisCacheService = $redisCacheService;
        $this->messageBus = $messageBus;
    }

    /**
     * Retrieve all customers.
     *
     * @return CustomerResponseDTO[]
     */
    public function getAllCustomers(): array
    {
        $cacheKey = 'all_customers';

        // Fetch from redis cache if available
        if ($cachedCustomers = $this->redisCacheService->get($cacheKey)) {
            return json_decode($cachedCustomers, true);
        }

        $customers = $this->customerRepository->findAll();
        $customerDTOs = $this->customerResponseTransformer->transformFromObjects($customers);

        // Cache transformed customer data for 10 minutes
        $this->redisCacheService->set($cacheKey, $customerDTOs);

        return $customerDTOs;
    }

    /**
     * Create/Update a customer in the database and clear cache.
     *
     * @param Customer $customer
     * @return void
     */
    public function saveCustomer(Customer $customer): void
    {
        $type = 'updated';
        // Determine if the customer is new (no ID) and needs to be persisted
        if (!$this->entityManager->contains($customer)) {
            $type = 'created';
            $this->entityManager->persist($customer);
        }

        $this->entityManager->flush();

        $this->clearCustomerCache();

        $this->notifyCustomerCreation($customer->getId(), $type);
    }

    /**
     * Dispatches a notification for customer creation.
     *
     * @param int $customerId
     * @param string $type
     * @return void
     */
    private function notifyCustomerCreation(int $customerId, string $type): void
    {
        $this->messageBus->dispatch(new CustomerNotificationMessage($customerId, $type));
    }

    /**
     * Remove a customer from the database and clear cache.
     *
     * @param Customer $customer
     * @return void
     */
    public function removeCustomer(Customer $customer): void
    {
        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        $this->clearCustomerCache();
    }

    /**
     * Clear cache for all customers.
     */
    private function clearCustomerCache(): void
    {
        $this->redisCacheService->delete('all_customers');
    }

    /**
     * Get statistics of all customers having cart, with caching.
     *
     * @return array
     */
    public function getAllCustomerStatistics(): array
    {
        $cacheKey = 'all_customers_statistics';

        // Fetch from redis cache if available
        if ($cachedCustomersStatistics = $this->redisCacheService->get($cacheKey)) {
            return json_decode($cachedCustomersStatistics, true);
        }

        $customers = $this->customerRepository->findCustomersWithCarts();
        $customerStatisticsDTOs = $this->customerStatisticResponseTransformer->transformFromObjects($customers);

        // Convert each DTO to an array
        $customerStatisticsArray = array_map(
            fn($dto) => $dto->toArray(),
            $customerStatisticsDTOs
        );


        $this->redisCacheService->set($cacheKey, $customerStatisticsArray);

        return $customerStatisticsArray;
    }

    /**
     * Get statistics of a customercart, with caching.
     *
     * @param Customer $customer
     * @return CustomerStatisticResponseDTO
     */
    public function getCustomerStatistics(Customer $customer): CustomerStatisticResponseDTO
    {
        $cacheKey = 'customer_' . $customer->getId() . '_statistics';

        // Fetch from redis cache if available
        if ($cachedCustomerStatistics = $this->redisCacheService->get($cacheKey)) {
            return $this->customerStatisticResponseTransformer->transformFromArray(json_decode($cachedCustomerStatistics, true));
        }

        $customerStatisticsDTO = $this->customerStatisticResponseTransformer->transformFromObject($customer);

        $this->redisCacheService->set($cacheKey, $customerStatisticsDTO);

        return $customerStatisticsDTO;
    }

    /**
     * Check if a customer has a cart.
     *
     * @param Customer $customer
     * @return bool
     */
    public function customerCartExists(Customer $customer): bool
    {
        return $customer->getCart() !== null;
    }
}