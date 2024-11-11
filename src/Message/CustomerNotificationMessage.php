<?php

namespace App\Message;

class CustomerNotificationMessage
{
    private int $customerId;
    private string $type;

    /**
     * @param int $customerId
     * @param string $type
     */
    public function __construct(int $customerId, string $type)
    {
        $this->customerId = $customerId;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}