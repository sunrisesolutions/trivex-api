<?php

declare(strict_types=1);

namespace App\Util;

use Aws\Exception\AwsException;
use Aws\Result;
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

    private $topics = [];

    private $normalizer;

    const MESSAGE_VERSION = 1;

    public function __construct(Sdk $sdk, iterable $config, iterable $credentials, string $env, ObjectNormalizer $normalizer)
    {
        $this->client = $sdk->createSns($config + $credentials);

        $this->sdk = $sdk;
        $this->applicationName = BaseUtil::PROJECT_NAME.'_'.BaseUtil::APP_NAME;
        $this->env = $env;
        $this->queuePrefix = $this->applicationName.'_'.$env.'_';
        $this->normalizer = $normalizer;
    }

    public function getTopicArn($name = null)
    {
        if (empty($name)) {
            return getenv('AWS_SNS_PREFIX').BaseUtil::PROJECT_NAME.'_'.BaseUtil::APP_NAME.'_'.strtoupper(getenv('APP_ENV'));
        }

        if (!empty($this->topics)) {
            if (array_key_exists($name, $this->topics)) {
                return $this->topics[$name]['TopicArn'];
            }
        }

        $topicResults = $this->listTopics();
        $topics = $topicResults->get('Topics');
        $arn = null;
        foreach ($topics as $topic) {
            $this->topics[$name] = $topic['TopicArn'];
            if (StringUtil::endsWith($topic['TopicArn'], $name)) {
                $arn = $topic['TopicArn'];
            }
        }

        return $arn;
    }

    /**
     * @param $name
     * @return \Aws\Result
     */
    public function createTopic($name)
    {
        return $this->client->createTopic([
            'Name' => $this->createTopicName($name)]);
    }

    public function subscribeQueueToTopic($queueArn, $topicArn)
    {
        $protocol = 'sqs';
        $endpoint = $queueArn;
        $topic = $topicArn;

        try {
            $result = $this->client->subscribe([
                'Protocol' => $protocol,
                'Endpoint' => $endpoint,
                'ReturnSubscriptionArn' => true,
                'TopicArn' => $topic,
            ]);
            var_dump($result);
            return $result;
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
            return null;
        }
    }

    public function hasQueueSubscription($topicArn, $name)
    {
        $subs = $this->listSubscriptionsByTopic($topicArn)->get('Subscriptions');

        foreach ($subs as $sub) {
            $ep = $sub['Endpoint'];
            if ($sub['Protocol'] !== 'sqs') {
                continue;
            }
            if (StringUtil::endsWith($ep, $name)) {
                return true;
            };
        }

        return false;
    }

    public function listSubscriptionsByTopic($arn)
    {
        return $this->client->listSubscriptionsByTopic(['TopicArn' => $arn]);
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

    /**
     * @param array $options
     * @return \Aws\Result
     */
    public function listTopics($options = [])
    {
        try {
            $result = $this->client->listTopics($options);
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

    private function createTopicName(string $name = null): string
    {
        if (empty($name)) {
            return sprintf(
                '%s_%s_%s', // TRIVEX_USER_DEV
                strtoupper($this->applicationName),
                $name,
                strtoupper($this->env)
            );
        } else {
            return sprintf(
                '%s', // TRIVEX_USER_DEV
                $name
            );
        }
    }

}
