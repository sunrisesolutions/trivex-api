<?php

namespace App\Tests\Api;

use App\Entity\Nationality;
use App\Entity\Person;
use App\Message\Entity\V1\PersonMessage;
use App\Security\JWTUser;
use App\Util\AwsSqsUtil;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Message\Message;

class PersonTest extends WebTestCase
{
    use RefreshDatabaseTrait;

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
        $this->queueName = 'PERSON';
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

    public function testPostPerson()
    {
        $givenName = 'special_unique_given_name';
        $content = [
            'birthDate' => '2019-07-04T07:20:21.114Z',
            'givenName' => $givenName,
            'familyName' => 'faname',
            'gender' => 'MALE',
            'email' => 'special_unique@gmail.com',
            'phoneNumber' => '0123456',
            'uuid' => 'UID-1234',
            'middleName' => 'midname'
        ];

        $response = $this->request('POST', '/people', json_encode($content), ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(201, $response->getStatusCode());

        sleep(7);

        /** @var PersonMessage $message */
        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($givenName, $message->data->person->givenName);
    }

    public function testPutPerson()
    {
        $givenName = 'person4';
        $emailToChange = 'changed@gmail.com';
        $personRepo = static::$container->get('doctrine')->getRepository(Person::class);
        $person = $personRepo->findOneBy(['givenName' => $givenName]);
        $this->assertEquals('person4@gmail.com', $person->getEmail());

        $token = $this->jwtToken();
        $userId = $person->getId();
        $content = [
            'birthDate' => '2019-07-04T07:20:21.115Z',
            'givenName' => $givenName,
            'familyName' => 'family name',
            'gender' => 'FEMALE',
            'email' => $emailToChange,
            'phoneNumber' => '123456',
            'uuid' => 'UID-789',
            'middleName' => 'midname2',
        ];

        $response = $this->request('PUT', 'people/' . $userId, json_encode($content), ['Authorization' => 'Bearer ' . $token]);
        $this->assertEquals(200, $response->getStatusCode());

        sleep(7);

        /** @var PersonMessage $message */
        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($givenName, $message->data->person->givenName);
        $this->assertEquals($emailToChange, $message->data->person->email);
    }

    public function testDeletePerson()
    {
        $personRepo = static::$container->get('doctrine')->getRepository(Person::class);
        $person = $personRepo->findOneBy([], ['id' => 'DESC']);
        $givenName = $person->getGivenName();
        $this->assertNotEmpty($person);

        $response = $this->request('DELETE', 'people/' . $person->getId(), null, ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(204, $response->getStatusCode());

        sleep(7);

        /** @var PersonMessage $message */
        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($givenName, $message->data->person->givenName);
    }

    protected function jwtToken(): string
    {
        $requestStack = static::$container->get('request_stack');
        $requestStack->push(new Request([], [], [], [], [], ['REMOTE_ADDR' => '10.10.10.10']));
        $jwtManager = static::$container->get('lexik_jwt_authentication.jwt_manager');
        $user = new JWTUser('admin', ['ROLE_ADMIN'], '123', '456', 'U1-024290123');
        return $jwtManager->create($user);
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