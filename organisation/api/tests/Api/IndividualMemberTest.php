<?php

namespace App\Tests\Api;

use App\Entity\Organisation;
use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Security\JWTUser;
use App\Entity\IndividualMember;

class IndividualMemberTest extends WebTestCase {

    //use RefreshDatabaseTrait;

    private $client;

    function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->client = static::createClient();
    }

    public function testInvididualMemberPost() {
        $doctrine = static::$container->get('doctrine');
        $org = $doctrine->getRepository(Organisation::class)->findOneBy([], ['id' => 'ASC']);
        $this->assertNotEmpty($org);
        $person = $doctrine->getRepository(Person::class)->findOneBy([], ['id' => 'DESC']);
        $this->assertNotEmpty($person);

        $content = [
            'organisationUuid' => $org->getUuid(),
            'personUuid' => $person->getUuid(),
            'admin' => true,
        ];
        $response = $this->request('POST', 'individual_members', json_encode($content), ['Authorization' => 'Bearer ' . $this->jwtToken()]);
        $this->assertEquals(201, $response->getStatusCode());
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