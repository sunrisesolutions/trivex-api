<?php

declare(strict_types=1);

namespace App\Util;

use Aws\Sdk;
use Aws\Sqs\SqsClient;

class AwsSnsUtil
{
    private $queuePrefix = 'INANZZZ_';

    /** @var SqsClient */
    private $client;
    private $sdk;
    private $applicationName;
    private $env;

    public function __construct(Sdk $sdk, iterable $config, iterable $credentials, string $applicationName, string $env)
    {
        $this->client = $sdk->createSns($config + $credentials);

        $this->sdk = $sdk;
        $this->applicationName = $applicationName;
        $this->env = $env;
        $this->queuePrefix = $this->applicationName.'_'.$env.'_';
    }

    public function publishMessage($message, $topicArn)
    {
        $this->client->publish(['Message' => $message, 'TopicArn' => $topicArn]);
    }
}
