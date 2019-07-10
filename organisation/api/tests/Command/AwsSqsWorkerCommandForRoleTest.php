<?php

namespace App\Tests\Api;

use App\Entity\Organisation;
use App\Entity\Role;
use App\Security\JWTUser;
use App\Util\AwsSqsUtil;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Message\Message;
use App\Util\AppUtil;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;


class AwsSqsWorkerCommandForRoleTest extends WebTestCase
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
        $this->queueName = 'AUTH'; //TRIVEX_ORG_TEST_AUTH
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

    public function testRolePost() {
        $org = static::$container->get('doctrine')->getRepository(Organisation::class)->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($org);

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

        $authAr = [
            'uuid' => 'AUTH-' . time(),
            'name' => 'ROLE_ORG_ADMIN',
            'organisationUuid' => $org->getUuid(),
            '_SYSTEM_OPERATION' => Message::OPERATION_POST
        ];

        $data = [];
        $data['data']['acrole'] = $authAr;
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

        $roleRepo = static::$container->get('doctrine')->getRepository(Role::class);
        $role = $roleRepo->findOneBy(['uuid' => $authAr['uuid']]);
        $this->assertNotEmpty($role);
    }

    public function RolePut() {
        $roleRepo = static::$container->get('doctrine')->getRepository(Role::class);
        $role = $roleRepo->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($role);

        $authAr = [
            'uuid' => $role->getUuid(),
            'name' => 'ROLE_USER',
            '_SYSTEM_OPERATION' => Message::OPERATION_PUT,
        ];

        $data = [];
        $data['data']['authorisation'] = $authAr;
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

        $new = $roleRepo->find($role->getId());
        $this->assertNotEmpty($new);
        $this->assertEquals('ROLE_USER', $new->getName());
    }

    public function RoleRemove() {
        $acRoleRepo = static::$container->get('doctrine')->getRepository(Role::class);
        $acrole = $acRoleRepo->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($acrole);

        $serializer = static::$container->get('serializer');
        $authAr = json_decode($serializer->serialize($acrole, 'json', ['ignored_attributes' => ['organisation']]), true);
        $authAr['_SYSTEM_OPERATION'] = Message::OPERATION_DELETE;

        $data = [];
        $data['data']['authorisation'] = $authAr;
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

        $acrole = $acRoleRepo->findOneBy(['uuid' => $acrole->getUuid()]);
        $this->assertEmpty($acrole);
    }

    protected function jwtToken(): string
    {
        $requestStack = static::$container->get('request_stack');
        $requestStack->push(new Request([], [], [], [], [], ['REMOTE_ADDR' => '10.10.10.10']));
        $jwtManager = static::$container->get('lexik_jwt_authentication.jwt_manager');
        $user = new JWTUser('admin', ['ROLE_ADMIN'], '123', '456', 'U1-024290123');
        return $jwtManager->create($user);
    }
}