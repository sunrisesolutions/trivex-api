<?php

namespace App\Tests\Api;

use App\Entity\ACRole;
use App\Entity\Organisation;
use App\Security\JWTUser;
use App\Util\AwsSqsUtil;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Message\Message;
use App\Util\AppUtil;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;


class AwsSqsWorkerCommandForOrgTest extends WebTestCase
{
    //use RefreshDatabaseTrait;

    protected $client;

    protected $queueName;

    protected $queueUrl;

    /** @var AwsSqsUtil */
    protected $sqsUtil;

    function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->queueName = 'ORG';
        $this->sqsUtil = static::$container->get('app_util_aws_sqs_util');
        $this->queueUrl = $this->sqsUtil->getQueueUrl($this->queueName);
        $this->purgeQueue();
    }

    protected function purgeQueue()
    {
        while (!empty($message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName))) {
            $this->sqsUtil->deleteMessage($message);
        }
    }

    public function testOrgPost() {
        $msg = [
            'Type' => 'Notification',
            'MessageId' => '22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Subject' => 'My First Message',
            'Message' => [],
            'Timestamp' => '2012-05-02T00:54:06.655Z',
            'SignatureVersion' => '1',
            'Signature' => 'EXAMPLEw6JRNwm1LFQL4ICB0bnXrdB8ClRMTQFGBqwLpGbM78tJ4etTwC5zU7O3tS6tGpey3ejedNdOJ+1fkIp9F2/LmNVKb5aFlYq+9rk9ZiPph5YlLmWsDcyC5T+Sy9/umic5S0UQc2PEtgdpVBahwNOdMW4JPwk0kAJJztnc=',
            'SigningCertURL' => 'https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem',
            'UnsubscribeURL' => 'https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96'
        ];

        $random = rand(10, 9999) . time();
        $orgAr = [
            'uuid' => 'UID-' . $random,
            'name' => 'name-' . $random,
            '_SYSTEM_OPERATION' => Message::OPERATION_POST
        ];

        $data = [];
        $data['data']['organisation'] = $orgAr;
        $data['version'] = 1; //AppUtil::MESSAGE_VERSION
        $msg['Message'] = json_encode($data);

        $this->sqsUtil->sendMessage($this->queueUrl, json_encode($msg));

        $kernel = static::createKernel();
        $app = new Application($kernel);
        $command = $app->find('app:aws-sqs-worker');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--queue' => $this->queueName,
            '--limit' => 1,
            '--env' => 'test'
        ]);

        $orgRepo = static::$container->get('doctrine')->getRepository(Organisation::class);
        $org = $orgRepo->findOneBy(['uuid' => $orgAr['uuid']]);
        $this->assertNotEmpty($org);
        $roles = $org->getRoles();
        $this->assertEquals(3, count($roles));
    }
}