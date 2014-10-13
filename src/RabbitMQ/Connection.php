<?php

namespace Riges\PhpRabbitMQLib\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;

class Connection
{
    /**
     * @var AMQPConnection $rabbitMQ
     */
    private $rabbitMQ;

    /**
     * @var AMQPChannel $channel
     */
    private $channel;

    /**
     * @var string $queueName
     */
    private $queueName;

    /**
     * @param AMQPConnection $rabbitMQ
     * @param string $queueName
     */
    public function __construct($rabbitMQ, $queueName)
    {
        $this->rabbitMQ = $rabbitMQ;
        $this->queueName = $queueName;
    }

    /**
     * @return AMQPConnection
     */
    public function getRabbitMQ()
    {
        return $this->rabbitMQ;
    }

    /**
     * @param AMQPConnection $rabbitMQ
     */
    public function setRabbitMQ($rabbitMQ)
    {
        $this->rabbitMQ = $rabbitMQ;
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param AMQPChannel $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

    public function close()
    {
        $this->getChannel()->close();
        $this->getRabbitMQ()->close();
    }


    /**
     *
     */
    public function queueDeclare()
    {
        $this->setChannel($this->getRabbitMQ()->channel());
        $this->getChannel()->queue_declare(
            $this->getQueueName(), // name
            false,   // passive=false : can be used to check if a queue exists without actually creating it
            true,   // durable=false : the queue will not survive server restart
            false,   // exclusive=false : the queue is not exclusive to this connection
            false // auto_delete=false : the queue will not be auto deleted
        );
    }
} 