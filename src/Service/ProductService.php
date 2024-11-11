<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Response\Transformer\ProductResponseDTOTransformer;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;
    private RedisCacheService $redisCacheService;
    private ProductResponseDTOTransformer $productResponseTransformer;

    public function __construct(
        EntityManagerInterface $entityManager,
        RedisCacheService $redisCacheService,
        ProductResponseDTOTransformer  $productResponseTransformer
    )
    {
        $this->entityManager = $entityManager;
        $this->redisCacheService = $redisCacheService;
        $this->productResponseTransformer = $productResponseTransformer;
    }

    /**
     * Retrieve all products with caching.
     *
     * @return Product[]
     */
    public function getAllProducts(): array
    {
        // Define a cache key for all products
        $cacheKey = 'all_products';

        // Check Redis for cached data
        if ($cachedProducts = $this->redisCacheService->get($cacheKey)) {
            return json_decode($cachedProducts, true);
        }

        // Retrieve products from the database if not cached
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $customerDTOs = $this->productResponseTransformer->transformFromObjects($products);

        // Cache the products for future requests
        $this->redisCacheService->set($cacheKey, $customerDTOs, 2592000); // Cache for 10 minutes

        return $customerDTOs;
    }

    /**
     * Save a product and clear the product cache.
     */
    public function saveProduct(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        // Clear product cache since products have been modified
        $this->clearProductCache();
    }

    /**
     * Remove a product and clear the product cache.
     */
    public function removeProduct(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        // Clear product cache since products have been modified
        $this->clearProductCache();
    }

    /**
     * Clear all cached products.
     *
     * @return void
     */
    private function clearProductCache(): void
    {
        $this->redisCacheService->delete('all_products');
    }
}