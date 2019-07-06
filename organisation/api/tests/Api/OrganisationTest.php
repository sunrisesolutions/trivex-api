<?php

namespace App\Tests\Api;

use App\Entity\Organisation;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Doctrine\Tests\Common\DataFixtures\StateFixture;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Security\JWTUser;
use App\Message\Message;
use App\Util\AwsSqsUtil;
use App\Message\Entity\V1\OrganisationMessage;

class OrganisationTest extends WebTestCase
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

    public function testPostOrg() {
        $name = 'donal trump';
        $content = [
            'foundedOn' => '2019-07-05T07:16:22.184Z',
            'type' => 'unknow type',
            'address' => '777 white house',
            'name' => $name,
            'logoName' => 'logo text here'
        ];
        $response = $this->request('POST', 'organisations', json_encode($content), ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(201, $response->getStatusCode());

        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($message->data->organisation->name, $name);
    }

    public function testPutOrg() {
        $orgRepo = static::$container->get('doctrine')->getRepository(Organisation::class);
        $org = $orgRepo->findOneBy(['uuid' => 'UID-4444']);

        $changedAddr = '372 CMT8';
        $content = [
            'foundedOn' => '2019-07-05T07:16:22.184Z',
            'type' => 'know type',
            'address' => $changedAddr,
            'name' => 'hoa hung',
            'logoName' => 'logo name'
        ];
        $response = $this->request('PUT', 'organisations/' . $org->getId(), json_encode($content), ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(200, $response->getStatusCode());

        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($message->data->organisation->address, $changedAddr);
    }

    public function testDeleteOrg() {
        $orgRepo = static::$container->get('doctrine')->getRepository(Organisation::class);
        $org = $orgRepo->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($org);
        $orgUid = $org->getUuid();

        $response = $this->request('DELETE', 'organisations/' . $org->getId(), null, ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(204, $response->getStatusCode());

        $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
        $this->assertNotEmpty($message);
        $this->assertEquals($message->data->organisation->uuid, $orgUid);
    }

    protected function purgeMessage(Message $message, $condition) {
        while(!empty($message) && is_callable($condition)) {
            if ($condition($message) === true) {
                return true;
            } else {
                $this->sqsUtil->deleteMessage($message);
                sleep(4);
                $message = $this->sqsUtil->receiveMessage($this->queueUrl, $this->queueName);
            }
        }
        return false;
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

            $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }
}