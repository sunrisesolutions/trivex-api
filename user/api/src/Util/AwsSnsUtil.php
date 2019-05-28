<?php

declare(strict_types=1);

namespace App\Util;

use App\Message\Message;
use Aws\Sdk;
use Aws\Sqs\SqsClient;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AwsSnsUtil
{
    /** @var SqsClient */
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
            $topicArn = AppUtil::TOPIC_ARN;
        }
        $this->client->publish(['Message' => $message, 'TopicArn' => $topicArn]);
        return true;
    }
}
