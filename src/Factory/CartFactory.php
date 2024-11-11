<?php

namespace App\Factory;

use App\Entity\Cart;
use App\Entity\Customer;
use DateTime;

class CartFactory
{
    /**
     * Initializes a Cart with options provided, including setting the Customer and date.
     *
     * @param array $options Array of options, expecting at least a 'customer' key.
     * @return Cart
     */
    public function createWithDefaults(array $options): Cart
    {
        $cart = new Cart();

        // Set customer
        if (isset($options['customer']) && $options['customer'] instanceof Customer) {
            $cart->setCustomer($options['customer']);
        }

       // Set default date
        $cart->setDateTime(new DateTime());

        return $cart;
    }
}