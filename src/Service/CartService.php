<?php

namespace App\Service;

use App\DTO\Response\CartResponseDTO;
use App\DTO\Response\Transformer\CartResponseDTOTransformer;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;

class CartService
{
    private EntityManagerInterface $entityManager;
    private RedisCacheService $redisCacheService;
    private CartResponseDTOTransformer $cartResponseTransformer;
    private SerializerInterface $serializer;

    /**
     * @param CartResponseDTOTransformer $cartResponseTransformer
     * @param EntityManagerInterface $entityManager
     * @param RedisCacheService $redisCacheService
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CartResponseDTOTransformer $cartResponseTransformer,
        EntityManagerInterface $entityManager,
        RedisCacheService $redisCacheService,
        SerializerInterface $serializer
    )
    {
        $this->cartResponseTransformer = $cartResponseTransformer;
        $this->entityManager = $entityManager;
        $this->redisCacheService = $redisCacheService;
        $this->serializer = $serializer;
    }

    /**
     * Retrieves the cart of the given Customer, using Redis cache if available.
     *
     * @param Customer $customer
     * @return array
     * @throws EntityNotFoundException
     */
    public function getCartByCustomer(Customer $customer): array
    {
        $cacheKey = 'cart_' . $customer->getId();

        // Check if the cart data is cached
        if ($cachedCart = $this->redisCacheService->get($cacheKey)) {
            return json_decode($cachedCart, true);
        }

        // If not cached, retrieve from the database
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['customer' => $customer]);

        if (!$cart) {
            throw new EntityNotFoundException('Cart not found for customer.');
        }

        // Transform to array
        $cartArray = $this->cartResponseTransformer->transformToArray($cart);

        // Cache the result for future requests
        $this->redisCacheService->set($cacheKey, $cartArray, 3600); // Cache for 1 hour

        return $cartArray;
    }

    /**
     * Saves a cart and clears the cache for the associated customer.
     *
     * @param Cart $cart
     * @return void
     */
    public function saveCart(Cart $cart): void
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        // Invalidate the cache for the customer’s cart
        $this->clearCartCache($cart->getCustomer());
    }

    /**
     * Removes a cart and clears the cache for the associated customer.
     *
     * @param Cart $cart
     * @return void
     */
    public function removeCart(Cart $cart): void
    {
        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        // Invalidate the cache for the customer’s cart
        $this->clearCartCache($cart->getCustomer());
    }

    /**
     * Retrieve the first product which is a free product.
     *
     * @return Product|object|null
     */
    public function getFreeProduct()
    {
        return $this->entityManager->getRepository(Product::class)->find(1);
    }

    /**
     * Clears the Redis cache for the customer's cart.
     *
     * @param Customer $customer
     * @return void
     */
    private function clearCartCache(Customer $customer): void
    {
        $this->redisCacheService->delete('cart_' . $customer->getId());
    }
}