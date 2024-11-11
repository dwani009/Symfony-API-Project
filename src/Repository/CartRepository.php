<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * Get the total count of products for a customer across all carts.
     * @param Customer $customer
     * @return int
     */
    public function getTotalCountByCustomer(Customer $customer): int
    {
        $qb = $this->createQueryBuilder('c');

        try {
            return (int) $qb
                ->select('COUNT(p.id)')
                ->join('c.products', 'p')
                ->where('c.customer = :customer')
                ->setParameter('customer', $customer)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * Get the total price of all products for a customer across all carts.
     *
     * @param Customer $customer
     * @return int
     */
    public function getTotalPriceByCustomer(Customer $customer): int
    {
        $qb = $this->createQueryBuilder('c');

        try {
            return (int) $qb
                ->select('SUM(p.price)')
                ->join('c.products', 'p')
                ->where('c.customer = :customer')
                ->andWhere('c.id IS NOT NULL')
                ->setParameter('customer', $customer)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return 0;
        }
    }
}