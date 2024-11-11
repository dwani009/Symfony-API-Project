<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\Transformer\CustomerResponseDTOTransformer;
use App\Entity\Customer;
use App\Form\Type\CustomerType;
use App\Service\CustomerService;
use App\Service\FormValidationService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/customers")
 */
class CustomerController extends AbstractApiController
{
    private CustomerService $customerService;
    private CustomerResponseDTOTransformer $customerResponseTransformer;
    private FormValidationService $formValidationService;

    public function __construct(
        CustomerService $customerService,
        CustomerResponseDTOTransformer $customerResponseTransformer,
        FormValidationService $formValidationService
    ) {
        $this->customerService = $customerService;
        $this->customerResponseTransformer = $customerResponseTransformer;
        $this->formValidationService = $formValidationService;
    }

    /**
     * Retrieve all customers.
     *
     * @Route("", name="api_customer_index", methods={"GET"})
     */
    public function indexAction(): Response
    {
        try {
            $customersDTO = $this->customerService->getAllCustomers();

            return $this->respond($customersDTO);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while fetching the customers list: ' . $e->getMessage()
            );
        }
    }

    /**
     * Create a new customer.
     *
     * @Route("", name="api_customer_create", methods={"POST"})
     *
     * @param Request $request The HTTP request object.
     * @throws ContainerExceptionInterface if the form validation fails.
     * @throws NotFoundExceptionInterface if the form validation fails.
     */
    public function createAction(Request $request): Response
    {
        try {
            $form = $this->buildForm(CustomerType::class);
            $form->handleRequest($request);

            $this->formValidationService->validateForm($form);

            /** @var Customer $customer */
            $customer = $form->getData();

            $this->customerService->saveCustomer($customer);

            $dto = $this->customerResponseTransformer->transformFromObject($customer);

            return $this->respond($dto, Response::HTTP_CREATED);

        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while creating the customer: ' . $e->getMessage()
            );
        }
    }

    /**
     * Update an existing customer.
     *
     * @Route("/{id}", name="api_customer_update", methods={"PUT", "PATCH"})
     *
     * @param Request $request The HTTP request object.
     * @param Customer $customer The customer entity instance to be updated.
     * @throws ContainerExceptionInterface if the form validation fails.
     * @throws NotFoundExceptionInterface if the form validation fails.
     */
    public function updateAction(Request $request, Customer $customer): Response
    {
        try {
            $form = $this->buildForm(CustomerType::class, $customer, [
                'method' => $request->getMethod(),
            ]);
            $form->handleRequest($request);

            $this->formValidationService->validateForm($form);

            $updatedCustomer = $form->getData();
            $this->customerService->saveCustomer($updatedCustomer);

            $dto = $this->customerResponseTransformer->transformFromObject($updatedCustomer);

            return $this->respond($dto);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while updating the customer: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove a customer.
     *
     * @Route("/{id}", name="api_customer_remove", methods={"DELETE"})
     *
     * @param Customer $customer The customer entity instance to be deleted.
     * @return Response JSON response confirming the customer removal.
     */
    public function removeAction(Customer $customer): Response
    {
        try{
            $this->customerService->removeCustomer($customer);

            return $this->respond('', Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while deleting the customer: ' . $e->getMessage()
            );
        }
    }
}