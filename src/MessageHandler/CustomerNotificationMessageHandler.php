<?php

namespace App\MessageHandler;

use App\Message\CustomerNotificationMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CustomerNotificationMessageHandler implements MessageHandlerInterface
{
    /**
     * @param CustomerNotificationMessage $message
     * @return void
     */
    public function __invoke(CustomerNotificationMessage $message)
    {
        // Simulate notification logic, e.g., sending an email or logging
        $customerId = $message->getCustomerId();
        $type = $message->getType();

        // For example, log message for test purposes
        echo "Handled notification for customer {$customerId} with type {$type}.\n";
    }
}