<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CustomerRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * Retrieve customers with at least one cart.
     *
     * @return Customer[]
     */
    public function findCustomersWithCarts(): array
    {
        $qb = $this->createQueryBuilder('customer')
            ->innerJoin('customer.cart', 'cart')
            ->addSelect('cart');

        return $qb->getQuery()->getResult();
    }
}