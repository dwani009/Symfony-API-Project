<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="app_customer")
 * @ORM\Entity()
 */
class Customer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=100)
     */
    private ?string $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length="50")
     */
    private ?string $phoneNumber;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Cart", mappedBy="customer", cascade={"remove"})
     */
    private ?Cart $cart = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return Cart|null
     */
    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    /**
     * @param Cart|null $cart
     * @return void
     */
    public function setCart(?Cart $cart): void
    {
        $this->cart = $cart;
    }
}