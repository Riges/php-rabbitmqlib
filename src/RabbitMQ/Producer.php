<?php

namespace Riges\PhpRabbitMQLib\RabbitMQ;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Producer extends Connection
{
    /**
     * @param AMQPConnection $rabbitMQ
     * @param string $queueName
     */
    public function __construct($rabbitMQ, $queueName)
    {
        parent::__construct($rabbitMQ, $queueName);
        $this->queueDeclare();
    }

    /**
     * @param string $message
     */
    public function publishMessage($message)
    {
        $msg = new AMQPMessage($message,
            array('delivery_mode' => 2) # make message persistent
        );

        $this->getChannel()->basic_publish($msg, '', $this->getQueueName());
    }
} 