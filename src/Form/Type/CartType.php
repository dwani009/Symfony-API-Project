<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Cart;
use App\Entity\Customer;
use App\Entity\Product;
use App\Factory\CartFactory;
use App\Service\CartService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CartType extends AbstractType
{
    private CartFactory $cartFactory;
    private CartService $cartService;

    /**
     * @param CartFactory $cartFactory
     * @param CartService $cartService
     */
    public function __construct(CartFactory $cartFactory, CartService $cartService)
    {
        $this->cartFactory = $cartFactory;
        $this->cartService = $cartService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('products', EntityType::class, [
                'class' => Product::class,
                'multiple' => true,
                'constraints' => [
                    new NotNull(),
                ],
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Cart $cart */
            $cart = $event->getData();
            $products = $cart->getProducts();
            $freeProduct = $this->cartService->getFreeProduct();

            // If there is a free product to apply
            if ($freeProduct) {
                $hasFreeProduct = $products->contains($freeProduct);

                // Remove the free product if it doesn't meet the condition
                if ($hasFreeProduct && count($products) <= 1) {
                    $cart->removeProduct($freeProduct);
                }

                // Add the free product if there are more than 1 product and it’s not already added
                if (count($products) > 1 && !$hasFreeProduct) {
                    $cart->addProduct($freeProduct);
                }
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cart::class,
            'csrf_protection' => false,
            'empty_data' => function ($form) {
                $options = $form->getConfig()->getOptions();
                return $this->cartFactory->createWithDefaults($options);
            },
        ]);

        $resolver->setRequired('customer');
        $resolver->setAllowedTypes('customer', Customer::class);
    }
}