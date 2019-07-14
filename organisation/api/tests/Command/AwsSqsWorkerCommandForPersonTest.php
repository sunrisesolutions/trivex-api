<?php

namespace App\Tests\Command;

use App\Entity\Nationality;
use App\Entity\Person;
use App\Message\Entity\V1\PersonMessage;
use App\Security\JWTUser;
use App\Util\AwsSqsUtil;
use Faker\Provider\zh_CN\DateTime;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Message\Message;
use Doctrine\Common\Collections\Collection;
use App\Util\AppUtil;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AwsSqsWorkerCommandForPersonTest extends WebTestCase
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
        $this->client = static::createClient();
        $this->sqsUtil = static::$container->get('app_util_aws_sqs_util');
        $this->queueName = 'PERSON'; //TRIVEX_ORG_TEST_PERSON
        $this->queueUrl = $this->sqsUtil->getQueueUrl($this->queueName);
        $this->purgeQueue();
    }

    public function testPostPersonIMFail() {
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

        $ImAr = [
            'profilePicture' => 'https://trivesg.s3.ap-southeast-1.amazonaws.com/trivesg_prod/organisation/individual/profile-picture/ORG_IM-UUID-0.jpg?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIARCMP7TH5THMU5LW2%2F20190714%2Fap-southeast-1%2Fs3%2Faws4_request&X-Amz-Date=20190714T045911Z&X-Amz-SignedHeaders=host&X-Amz-Expires=604800&X-Amz-Signature=18792d576da7a6e19f70f4c13d3cbff927f88e84357246b75ce759a2735609ff',
            'admin' => false,
            'personData' => [],
            'id' => null,
            'uuid' => 'ORG_IM-5d2ab69fc90f9-125914072019',
            'createdAt' => '2019-07-14T12:59:11+08:00',
            'person' => null,
            'organisation' => null,
            'accessToken' => 'ORG_IMT-5d2ab69fc9109-125914072019',
            'roles' => [],
            'fromConnections' => [],
            'toConnections' => [],
            'fulltextString' => 'name:  email:  employer: ',
            'personUuid' => 'PERSON-5d2ab64cbc467-125714072019',
            'organisationUuid' => 'ORG-5d29bdcf201d1-191713072019',
            'emailBody' => null,
            'emailSubject' => null,
            '_SYSTEM_OPERATION' => 'POST'
        ];

        $data = [];
        $data['data']['individualMember'] = $ImAr;
        $data['version'] = AppUtil::MESSAGE_VERSION;
        $msg['Message'] = json_encode($data);

        $this->sqsUtil->sendMessage($this->queueUrl, json_encode($msg));
    }

    public function PostPerson() {
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
        $personAr = [
            'name' => 'name-' . $random,
            'email' => $random . '@gmail.com',
            'phoneNumber' => '84123456789',
            'uuid' => 'UID-' . $random,
            'jobTitle' => 'cheater',
            'employerName' => 'magenta',
            'birthDate' => new \DateTime("now"),
            '_SYSTEM_OPERATION' => Message::OPERATION_POST,
        ];
        $data = [];
        $data['data']['person'] = $personAr;
        $data['version'] = AppUtil::MESSAGE_VERSION;
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

        $personRepo = static::$container->get('doctrine')->getRepository(Person::class);
        $person = $personRepo->findOneBy(['uuid' => $personAr['uuid']]);
        $this->assertNotEmpty($person);
    }

    public function PutPerson()
    {
        $personRepo = static::$container->get('doctrine')->getRepository(Person::class);
        /** @var Person $person */
        $person = $personRepo->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($person);

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

        $serializer = static::$container->get('serializer');
        $randVal = 'name-'.rand(1, 9999).time();
        $person->setName($randVal);

        $personAr = json_decode($serializer->serialize($person, 'json'), true);
        $personAr['_SYSTEM_OPERATION'] = Message::OPERATION_PUT;

        $data = [];
        $data['data']['person'] = $personAr;
        $data['version'] = AppUtil::MESSAGE_VERSION;
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

        $person = $personRepo->findOneBy(['uuid' => $person->getUuid()]);
        $this->assertEquals($person->getName(), $randVal);
    }

    public function DeletePerson() {
        $personRepo = static::$container->get('doctrine')->getRepository(Person::class);
        $person = $personRepo->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($person);

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

        $serializer = static::$container->get('serializer');

        $personAr = json_decode($serializer->serialize($person, 'json'), true);
        $personAr['_SYSTEM_OPERATION'] = Message::OPERATION_DELETE;

        $data = [];
        $data['data']['person'] = $personAr;
        $data['version'] = AppUtil::MESSAGE_VERSION;
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

        $del = $personRepo->findOneBy(['uuid' => $person->getUuid()]);
        $this->assertEmpty($del);
    }

    protected function jwtToken(): string
    {
        $requestStack = static::$container->get('request_stack');
        $requestStack->push(new Request([], [], [], [], [], ['REMOTE_ADDR' => '10.10.10.10']));
        $jwtManager = static::$container->get('lexik_jwt_authentication.jwt_manager');
        $user = new JWTUser('admin', ['ROLE_ADMIN'], '123', '456', 'U1-024290123');
        return $jwtManager->create($user);
    }

    protected function purgeQueue()
    {
        while (!empty($message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName))) {
            $this->sqsUtil->deleteMessage($message);
        }
    }

    protected function request(string $method, string $uri, $content = null, array $headers = []): Response
    {
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $server['CONTENT_TYPE'] = $value;

                continue;
            }

            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }
}