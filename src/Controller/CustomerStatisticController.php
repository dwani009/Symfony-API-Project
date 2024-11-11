<?php

namespace App\Controller;

use App\DTO\Response\Transformer\CustomerStatisticResponseDTOTransformer;
use App\Entity\Customer;
use App\Service\CustomerService;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/customers")
 */
class CustomerStatisticController extends AbstractApiController
{
    private CustomerStatisticResponseDTOTransformer $customerStatisticResponseDTOTransformer;
    private CustomerService $customerService;

    /**
     * @param CustomerStatisticResponseDTOTransformer $customerStatisticResponseDTOTransformer
     * @param CustomerService $customerService
     */
    public function __construct(
        CustomerStatisticResponseDTOTransformer $customerStatisticResponseDTOTransformer,
        CustomerService  $customerService
    )
    {
        $this->customerStatisticResponseDTOTransformer = $customerStatisticResponseDTOTransformer;
        $this->customerService = $customerService;
    }

    /**
     * Retrieve statistics for a single customer.
     *
     * @Route("/{id}/statistics", name="api_customer_statistics_single", methods={"GET"})
     * @param Customer $customer
     * @return Response
     */
    public function singleStatisticsAction(Customer $customer): Response
    {
        try {
            if ($this->customerService->customerCartExists($customer)) {
                $customerDTO = $this->customerService->getCustomerStatistics($customer);

                return $this->respond($customerDTO);
            }

            return $this->respond('', Response::HTTP_NOT_FOUND, 'Customer does not have cart');
        } catch (Exception $exception) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while fetching the customer statistics: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Retrieve statistics for all customers.
     *
     * @Route("/statistics", name="api_customer_statistics_all", methods={"GET"})
     * @return Response
     */
    public function allStatisticsAction(): Response
    {
        try {
            $customersDTO = $this->customerService->getAllCustomerStatistics();

            return $this->respond($customersDTO);
        } catch (Exception $exception) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while fetching the all statistics: ' . $exception->getMessage()
            );
        }
    }
}