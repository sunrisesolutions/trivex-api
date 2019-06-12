<?php

declare(strict_types=1);

namespace App\Util;

use App\Message\Message;
use App\Message\MessageFactory;
use Aws\Result;
use Aws\Sdk;
use Aws\Sqs\SqsClient;
use App\Message\Entity as MessageEntity;

class AwsSqsUtil implements AwsSqsUtilInterface
{

    private $mf;

    private $queuePrefix = 'INANZZZ_';

    /** @var SqsClient */
    private $client;
    private $sdk;
    private $applicationName;
    private $env;

    public function __construct(MessageFactory $mf, Sdk $sdk, iterable $config, iterable $credentials, string $env)
    {
        $this->mf = $mf;
        $this->client = $sdk->createSqs($config + $credentials);
        $this->sdk = $sdk;
        $this->applicationName = AppUtil::PROJECT_NAME.'_'.AppUtil::APP_NAME;
        $this->env = $env;
        $this->queuePrefix = $this->applicationName.'_'.$env.'_';
    }

    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#createqueue
     */
    public function createQueue(string $name): ?string
    {
        /** @var Result $result */
        $result = $this->client->createQueue(['QueueName' => $this->queuePrefix.$name]);

        return $result->get('QueueUrl');
    }

    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#listqueues
     */
    public function listQueues(): iterable
    {
        $queues = [];

        /** @var Result $result */
        $result = $this->client->listQueues();
        foreach ($result->get('QueueUrls') as $queueUrl) {
            $queues[] = $queueUrl;
        }

        return $queues;
    }

    /**
     * @link https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#getqueueurl
     */
    public function getQueueUrl(string $name): ?string
    {
        /** @var Result $result */
        $result = $this->client->getQueueUrl([
            'QueueName' => $this->createQueueName($name),
        ]);

        return $result->get('QueueUrl');
    }

    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#sendmessage
     */
    public function sendMessage(string $url, string $message): ?string
    {
        /** @var Result $result */
        $result = $this->client->sendMessage([
            'QueueUrl' => $url,
            'MessageBody' => $message,
        ]);

        return $result->get('MessageId');
    }

    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#getqueueattributes
     */
    public function getTotalMessages(string $url): string
    {
        /** @var Result $result */
        $result = $this->client->getQueueAttributes([
            'QueueUrl' => $url,
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ]);

        return $result->get('Attributes')['ApproximateNumberOfMessages'];
    }

    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#purgequeue
     */
    public function purgeQueue(string $url): void
    {
        $this->client->purgeQueue(['QueueUrl' => $url]);
    }

    /**
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#deletequeue
     */
    public function deleteQueue(string $url): void
    {
        $this->client->deleteQueue(['QueueUrl' => $url]);
    }


    public function createClient(iterable $config, iterable $credentials): void
    {
        $this->client = $this->sdk->createSqs($config + $credentials);
    }

    /**
     * @link https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#receivemessage
     */
    public function receiveMessage(string $url, string $name): ?Message
    {
        /** @var Result $result */
        $result = $this->client->receiveMessage([
            'QueueUrl' => $url,
            'MaxNumberOfMessages' => 1,
        ]);

        $message = null;
        if (null !== $result->get('Messages')) {
            $body = $result->get('Messages')[0]['Body'];
            $id = $result->get('Messages')[0]['MessageId'];
            $receiptHandle = $result->get('Messages')[0]['ReceiptHandle'];
            $message = $this->mf->createMessage(AppUtil::getFullAppName($name), $url, $id, $body, $receiptHandle);

        }

        return $message;
    }

    /**
     * @link https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#deletemessage
     */
    public function deleteMessage(Message $message): void
    {
        $this->client->deleteMessage([
            'QueueUrl' => $message->url,
            'ReceiptHandle' => $message->receiptHandle,
        ]);
    }

    /**
     * @link https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#changemessagevisibility
     */
    public function requeueMessage(Message $message): void
    {
        $this->client->changeMessageVisibility([
            'QueueUrl' => $message->url,
            'ReceiptHandle' => $message->receiptHandle,
            'VisibilityTimeout' => 30,
        ]);
    }

    private function createQueueName(string $name, bool $isDeadLetter = null): string
    {
        return sprintf(
            '%s_%s_%s%s', // TRIVEX_USER_DEV_ORG
            strtoupper($this->applicationName),
            strtoupper($this->env),
            $name,
            $isDeadLetter ? '_DL' : null
        );
    }
}
