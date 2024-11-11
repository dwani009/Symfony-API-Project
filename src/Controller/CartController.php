<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\Transformer\CartResponseDTOTransformer;
use App\Entity\Customer;
use App\Entity\Cart;
use App\Form\Type\CartType;
use App\Service\CartService;
use App\Service\FormValidationService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/customers/{id}/cart")
 */
class CartController extends AbstractApiController
{
    private CartService $cartService;
    private CartResponseDTOTransformer $cartResponseDTOTransformer;
    private FormValidationService $formValidationService;

    public function __construct(
        CartService $cartService,
        CartResponseDTOTransformer $cartResponseDTOTransformer,
        FormValidationService $formValidationService
    ) {
        $this->cartService = $cartService;
        $this->cartResponseDTOTransformer = $cartResponseDTOTransformer;
        $this->formValidationService = $formValidationService;
    }

    /**
     * Retrieve the cart for a specific customer.
     *
     * @Route("", name="api_customer_cart_show", methods={"GET"})
     *
     * @param Customer $customer The customer whose cart is to be retrieved.
     * @return Response JSON response containing the customer's cart details.
     */
    public function showAction(Customer $customer): Response
    {
        try {
            $cartDTO = $this->cartService->getCartByCustomer($customer);

            return $this->respond($cartDTO);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while fetching the cart of a customer: ' . $e->getMessage()
            );
        }
    }

    /**
     * Create a new cart for the specified customer.
     *
     * @Route("", name="api_customer_cart_create", methods={"POST"})
     *
     * @param Request $request The HTTP request object.
     * @param Customer $customer The customer for whom the cart is created.
     * @return Response JSON response containing the created cart details.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface if the form validation fails.
     */
    public function createAction(Request $request, Customer $customer): Response
    {
        try {
            $form = $this->buildForm(CartType::class, null, [
                'customer' => $customer,
            ]);
            $form->handleRequest($request);

            $this->formValidationService->validateForm($form);

            $cart = $form->getData();
            $this->cartService->saveCart($cart);

            $dto = $this->cartResponseDTOTransformer->transformFromObject($cart);

            return $this->respond($dto, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while creating the cart: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Update an existing cart for a specific customer.
     *
     * @Route("/{cartId}", name="api_customer_cart_update", methods={"PUT", "PATCH"})
     * @ParamConverter("customer", class="App\Entity\Customer", options={"id" = "id"})
     * @ParamConverter("cart", class="App\Entity\Cart", options={"id" = "cartId"})
     *
     * @param Request $request The HTTP request object.
     * @param Customer $customer The customer who owns the cart.
     * @param Cart $cart The cart to be updated.
     * @return Response JSON response containing the updated cart details.
     * @throws ContainerExceptionInterface|NotFoundHttpException if the cart does not belong to the specified customer.
     */
    public function updateAction(Request $request, Customer $customer, Cart $cart): Response
    {
        try {
            // Check if the cart belongs to the customer
            if ($cart->getCustomer() !== $customer) {
                throw new NotFoundHttpException('Cart not found for the specified customer');
            }

            $form = $this->buildForm(CartType::class, $cart, [
                'method' => $request->getMethod(),
                'customer' => $customer,
            ]);
            $form->handleRequest($request);

            $this->formValidationService->validateForm($form);

            $updatedCart = $form->getData();
            $this->cartService->saveCart($updatedCart);

            $dto = $this->cartResponseDTOTransformer->transformFromObject($updatedCart);

            return $this->respond($dto);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while updating the cart: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified cart for a customer.
     *
     * @Route("/{cartId}", name="api_customer_cart_remove", methods={"DELETE"})
     * @ParamConverter("cart", options={"id" = "cartId"})
     *
     * @param Customer $customer The customer who owns the cart.
     * @param Cart $cart The cart to be removed.
     * @return Response JSON response confirming the cart removal.
     */
    public function removeAction(Customer $customer, Cart $cart): Response
    {
        try {
            // Verify that the cart belongs to the customer
            if ($cart->getCustomer() !== $customer) {
                return $this->respond('', Response::HTTP_NOT_FOUND, 'Cart not found for the specified customer');
            }

            $this->cartService->removeCart($cart);

            return $this->respond('', Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while deleting the cart: ' . $e->getMessage()
            );
        }
    }
}