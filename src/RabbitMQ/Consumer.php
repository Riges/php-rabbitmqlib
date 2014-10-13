<?php

namespace Riges\PhpRabbitMQLib\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Consumer extends Connection
{
    /**
     * @var string
     */
    private $workPath;

    /**
     * @var int
     */
    private $maxConsumers = 10;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var int
     */
    private $ttl = 1800;

    /**
     * @param AMQPConnection $rabbitMQ
     * @param string $queueName
     * @param string $workPath
     * @param int $maxConsumers
     * @param int $ttl
     */
    public function __construct($rabbitMQ, $queueName, $workPath, $maxConsumers = 10, $ttl = 1800)
    {
        parent::__construct($rabbitMQ, $queueName);

        $this->workPath = $workPath;
        $this->maxConsumers = $maxConsumers;
        $this->startTime = time();
        $this->ttl = $ttl;

        $this->queueDeclare();
    }

    /**
     *
     */
    public function launchConsumer()
    {
        $this->getChannel()->basic_qos(null, 1, null);
        $this->getChannel()->basic_consume($this->getQueueName(), '', false, false, false, false, array($this, 'consume'));

        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     *
     */
    public function queueDeclare()
    {
        $this->setChannel($this->getRabbitMQ()->channel());
        list(, , $consumerCount) = $this->getChannel()->queue_declare(
            $this->getQueueName(), // name
            false,   // passive=false : can be used to check if a queue exists without actually creating it
            true,   // durable=false : the queue will not survive server restart
            false,   // exclusive=false : the queue is not exclusive to this connection
            false // auto_delete=false : the queue will not be auto deleted
        );

        if ($consumerCount > $this->maxConsumers) {
            $this->close();
            exit;
        }
    }

    /**
     * Consume messages from queue
     *
     * @param AMQPMessage $message The message
     */
    public function consume(AMQPMessage $message)
    {
        // do whatever you have to do with your message
        $result = $this->executeWorker($message->body);

        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];

        if ($result === true) {
            // tell rabbitmq that message is completed
            $channel->basic_ack($message->delivery_info['delivery_tag']);
        }

        // stop consuming when ttl is reached
        if (($this->startTime + $this->ttl) < time()) {
            $channel->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }

    /**
     * Execute a worker in an external process
     *
     * @param string $body The job message body
     * @return boolean True on success
     */
    protected function executeWorker($body)
    {
        // open a php process and call the worker.php script
        $pipes = array();
        $process = proc_open(
            'php -d display_errors=stderr ' . $this->getWorkPath(),
            array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
            ),
            $pipes,
            sys_get_temp_dir(),
            null
        );

        if (is_resource($process)) {
            // write the message into worker.php stdin
            fwrite($pipes[0], $body);
            fclose($pipes[0]);

            // read errors from worker.php
            $stdErr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // if worker.php ends without errors, execution was successful
            if (proc_close($process) === 0 && empty($stdErr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getWorkPath()
    {
        return $this->workPath;
    }
} 