<?php

namespace App\Api\Tests;

use App\Entity\Nationality;
use App\Entity\Person;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Message\Message;

class PersonEventSubscriberTest extends WebTestCase {

    protected $client;

    function setUp() {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testPostPerson() {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $awsSnsUtil = $container->get('app_util_aws_sns_util');

        $content = [
//            'nationality' => 'vietnam',
            'birthDate' => '2019-07-04T07:20:21.114Z',
            'givenName' => 'giname',
            'familyName' => 'faname',
            'gender' => 'MALE',
            'email' => 'testmail@gmail.com',
            'phoneNumber' => '0123456',
            'uuid' => 'UID-1234',
            'middleName' => 'midname',
            'nationalities' => [
                [
                    'country' => '123',
                    'nricNumber' => '123',
                    'passportNumber' => '456',
                    'person' => 'per'
                ],
                [
                    'country' => '456',
                    'nricNumber' => '789',
                    'passportNumber' => '45654',
                    'person' => 'per2'
                ]
            ]
        ];
        $response = $this->request('POST', '/people', json_encode($content));
        $this->assertEquals(201, $response->getStatusCode());

        $topicArn = $awsSnsUtil->getTopicArn('TRIVEX_PERSON_TEST');
        //$this->awsSnsUtil->subscribeQueueToTopic('TRIVEX_PERSON_TEST', $topicArn);
        //$this->assertEquals(true, $awsSnsUtil->hasQueueSubscription($topicArn, 'TRIVEX_PERSON_TEST'));
//        $awsSnsUtil->publishMessage($person, Message::OPERATION_POST, $topicArn);

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