<?php

declare(strict_types=1);

namespace App\Util;

use App\Message\Message;
use Aws\Exception\AwsException;
use Aws\Sdk;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AwsSnsUtil
{
    /** @var SnsClient */
    private $client;
    private $sdk;
    private $applicationName;
    private $env;

    private $normalizer;

    const MESSAGE_VERSION = 1;

    public function __construct(Sdk $sdk, iterable $config, iterable $credentials, string $env, ObjectNormalizer $normalizer)
    {
        $this->client = $sdk->createSns($config + $credentials);

        $this->sdk = $sdk;
        $this->applicationName = AppUtil::PROJECT_NAME.'_'.AppUtil::APP_NAME;
        $this->env = $env;
        $this->queuePrefix = $this->applicationName.'_'.$env.'_';
        $this->normalizer = $normalizer;
    }

    public function getTopicArn()
    {
        return getenv('AWS_SNS_PREFIX').AppUtil::PROJECT_NAME.'_'.AppUtil::APP_NAME.'_'.strtoupper(getenv('APP_ENV'));
    }

    public function createTopic($name)
    {
        return $this->client->createTopic([
            'Name' => $name]);
    }

    public function deleteTopic($arn)
    {
        try {
            $result = $this->client->deleteTopic([
                'TopicArn' => $arn,
            ]);
            var_dump($result);
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
        return $result;
    }

    public function listTopics(){
        try {
            $result = $this->client->listTopics([
            ]);
            var_dump($result);
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
        return $result;
    }

    public function publishMessage($object, $topicArn = null)
    {
        if (is_string($object)) {
            $message = $object;
        } else {
            $messageArray = [];
            $className = (new \ReflectionClass($object))->getShortName();

            $messageArray['data'] = [lcfirst($className) => $this->normalizer->normalize($object)];
//        $first = $member->getOrganisationUsers()->first();

//        $message['data']['first'] = $this->normalizer->normalize($first);
            $messageArray['version'] = self::MESSAGE_VERSION;

            $message = json_encode($messageArray);
        }

        if (empty($topicArn)) {
            $topicArn = $this->getTopicArn();
        }
        $this->client->publish(['Message' => $message, 'TopicArn' => $topicArn]);
        return true;
    }
}
