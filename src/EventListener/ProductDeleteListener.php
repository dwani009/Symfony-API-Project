<?php

namespace App\EventListener;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ProductDeleteListener
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Product $product
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function preRemove(Product $product, LifecycleEventArgs $args): void
    {
        // Find all carts that contain this product
        $carts = $this->entityManager->getRepository(Cart::class)->findBy(['product' => $product->getId()]);

        foreach ($carts as $cart) {
            $this->entityManager->remove($cart);
        }

        $this->entityManager->flush();
    }
}