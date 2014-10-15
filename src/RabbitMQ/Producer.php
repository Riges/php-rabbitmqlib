<?php

namespace Riges\PhpRabbitMQLib\RabbitMQ;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Producer extends Connection
{
    /**
     * @param string $rabbitMQHost
     * @param string|int $rabbitMQPort
     * @param $rabbitMQUser
     * @param $rabbitMQPassword
     * @param string $queueName
     */
    public function __construct($rabbitMQHost, $rabbitMQPort = 5672, $rabbitMQUser, $rabbitMQPassword, $queueName)
    {
        parent::__construct($rabbitMQHost, $rabbitMQPort = 5672, $rabbitMQUser, $rabbitMQPassword, $queueName);
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