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
        $random = time();
        $content = [
            'givenName' => 'name-' . $random,
            'email' => $random . '@gmail.com',
            'phoneNumber' => '84123456789',
            'uuid' => 'UID-' . $random,
            'employerName' => 'magenta',
        ];

        $response = $this->request('POST', '/people', json_encode($content), ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(201, $response->getStatusCode());

        $person = static::$container->get('doctrine')->getRepository(Person::class)->findOneBy(['givenName' => $content['givenName']]);
        $this->assertNotEmpty($person);

        sleep(2);

        /** @var PersonMessage $message */
        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($content['givenName'], $message->data->person->givenName);
        $this->assertEquals($content['email'], $message->data->person->email);
    }

    public function PutPerson()
    {
        $person = static::$container->get('doctrine')->getRepository(Person::class)->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($person);

        $random = time();
        $content = [
            'givenName' => 'name-' . $random,
            'email' => $random . '@gmail.com',
            'phoneNumber' => '84123456789',
            'employerName' => 'magenta',
        ];

        $response = $this->request('PUT', 'people/' . $person->getId(), json_encode($content), ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(200, $response->getStatusCode());

        sleep(2);

        $changed = static::$container->get('doctrine')->getRepository(Person::class)->find($person->getId());
        $this->assertNotEmpty($changed);
        $this->assertEquals($content['givenName'], $changed->getGivenName());
        $this->assertEquals($content['email'], $changed->getEmail());

        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($person->getUuid(), $message->data->person->uuid);
        $this->assertEquals($content['email'], $message->data->person->email);
    }

    public function DeletePerson()
    {
        $person = static::$container->get('doctrine')->getRepository(Person::class)->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($person);
        $id = $person->getId();

        $response = $this->request('DELETE', 'people/' . $id, null, ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(204, $response->getStatusCode());

        sleep(2);

        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($person->getGivenName(), $message->data->person->givenName);

        $rm = static::$container->get('doctrine')->getRepository(Person::class)->find($id);
        $this->assertEmpty($rm);
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