<?php

namespace App\Service;

use App\Entity\SnsSubscription;
use App\Util\BaseUtil;
use App\Util\AwsSnsUtil;
use App\Util\AwsSqsUtil;
use App\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AppService
{
    private $snsUtil;
    private $sqsUtil;
    private $registry;
    private $topicArns = [];
    private $topicEndpoints = [];

    public function __construct(AwsSnsUtil $snsUtil, AwsSqsUtil $sqsUtil, RegistryInterface $registry)
    {
        $this->snsUtil = $snsUtil;
        $this->sqsUtil = $sqsUtil;
        $this->registry = $registry;
    }

    public function getTopicArns($env = 'DEV')
    {
        if (count($this->topicArns) === 0) {
            $repo = $this->registry->getRepository(SnsSubscription::class);
            $subs = $repo->findAll();
            /** @var SnsSubscription $sub */
            foreach ($subs as $sub) {
                $key = $sub->getTopic();
                if (!array_key_exists($key, $this->topicArns)) {
                    $topicName = BaseUtil::PROJECT_NAME.'_'.$key.'_'.$env;
                    $this->topicArns[$key] = $this->snsUtil->getTopicArn($topicName);
                    if (!array_key_exists($key, $this->topicEndpoints)) {
                        $this->topicEndpoints[$key] = [];
                    }
                    $this->topicEndpoints[$key][] = ['endpoint' => $sub->getEndpoint(), 'protocol' => $sub->getProtocol()];
                };
            }
        }
        return $this->topicArns;
    }

    public function listTopics()
    {
        $results = $this->snsUtil->listTopics([
        ]);

        return $results;
    }

    public function initiateTopics($env = 'DEV')
    {
        $env = strtoupper($env);
        $topicArns = $this->getTopicArns($env);
        $topics = [];
        foreach ($topicArns as $name => $arn) {
            if (empty($arn)) {
                $topicName = BaseUtil::PROJECT_NAME.'_'.strtoupper($name).'_'.$env;
                $r = $this->snsUtil->createTopic($topicName);
                $this->topicArns[$name] = $r->get('TopicArn');
            }
            $topicArn = $this->topicArns[$name];
            $subscribers = $this->topicEndpoints[$name];

            foreach ($subscribers as $index => $subscriber) {
                $queueCode = $this->topicEndpoints[$name][$index]['endpoint'];
                $queuePrefix = BaseUtil::PROJECT_NAME.'_'.strtoupper($queueCode).'_'.$env.'_';
                $queueName = $this->sqsUtil->createQueueName($queueCode,$queuePrefix);

                if (empty($this->snsUtil->hasQueueSubscription($topicArn, $queueName))) {
                    $queueUrl = $this->sqsUtil->createQueue($name, $queuePrefix);
                    $queueArn = $this->sqsUtil->getQueueArn($queueUrl);
                    $this->snsUtil->subscribeQueueToTopic($queueArn, $topicArn);
                    $this->topicEndpoints[$name][$index]['url'] = $queueUrl;
                } else {
                    $queueUrl = $this->sqsUtil->getQueueUrl($name, $queuePrefix);
                }
                if (!array_key_exists($name, $topics)) {
                    $topics[$name] = ['arn' => $arn, 'subscription' => []];
                }
                $topics[$name]['subscription'][] = ['protocol' => $subscriber['protocol'],
                    'endpoint' => $subscriber['endpoint'],
                    'url' => $queueUrl];
            }
        }
        return $topics;
    }
}