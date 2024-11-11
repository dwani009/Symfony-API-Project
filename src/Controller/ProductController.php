<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\Transformer\ProductResponseDTOTransformer;
use App\Entity\Product;
use App\Form\Type\ProductType;
use App\Service\ProductService;
use App\Service\FormValidationService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/products")
 */
class ProductController extends AbstractApiController
{
    private ProductService $productService;
    private ProductResponseDTOTransformer $productResponseDTOTransformer;
    private FormValidationService $formValidationService;

    public function __construct(
        ProductService $productService,
        ProductResponseDTOTransformer $productResponseDTOTransformer,
        FormValidationService $formValidationService
    ) {
        $this->productService = $productService;
        $this->productResponseDTOTransformer = $productResponseDTOTransformer;
        $this->formValidationService = $formValidationService;
    }

    /**
     * Retrieve all products.
     *
     * @Route("", name="api_product_index", methods={"GET"})
     *
     * @return Response JSON response containing all products details.
     */
    public function indexAction(): Response
    {
        try {
            $dto = $this->productService->getAllProducts();

            return $this->respond($dto);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while fetching the products list: ' . $e->getMessage()
            );
        }
    }

    /**
     * Create a new product.
     *
     * @Route("", name="api_product_create", methods={"POST"})
     *
     * @param Request $request
     * @return Response JSON response containing the created product details.
     * @throws ContainerExceptionInterface if the form validation fails.
     * @throws NotFoundExceptionInterface if the form validation fails.
     */
    public function createAction(Request $request): Response
    {
        try {
            $form = $this->buildForm(ProductType::class);
            $form->handleRequest($request);

            $this->formValidationService->validateForm($form);

            /** @var Product $product */
            $product = $form->getData();
            $this->productService->saveProduct($product);

            $dto = $this->productResponseDTOTransformer->transformFromObject($product);

            return $this->respond($dto, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while creating the product: ' . $e->getMessage()
            );
        }
    }

    /**
     * Update an existing product.
     *
     * @Route("/{id}", name="api_product_update", methods={"PUT", "PATCH"})
     *
     * @param Request $request The HTTP request object.
     * @param Product $product
     * @return Response JSON response containing the updated product details.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateAction(Request $request, Product $product): Response
    {
        try {
            $form = $this->buildForm(ProductType::class, $product, [
                'method' => $request->getMethod(),
            ]);
            $form->handleRequest($request);

            $this->formValidationService->validateForm($form);

            /** @var Product $updatedProduct */
            $updatedProduct = $form->getData();
            $this->productService->saveProduct($updatedProduct);

            $dto = $this->productResponseDTOTransformer->transformFromObject($updatedProduct);

            return $this->respond($dto);
        } catch (Exception $e) {
            return $this->respond(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An error occurred while updating the product: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove a product.
     *
     * @Route("/{id}", name="api_product_remove", methods={"DELETE"})
     *
     * @param Product $product
     * @return Response JSON response confirming the product removal.
     */
    public function removeAction(Product $product): Response
    {
        try {
            $this->productService->removeProduct($product);

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