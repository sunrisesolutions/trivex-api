<?php


namespace App\Util\Authorisation;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sdk;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\JWTUser;
use GuzzleHttp\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ApiResourceUtil
{
    /** @var SnsClient */
    private $client;
    private $sdk;
    private $applicationName;
    private $env;

    private $topics = [];

    private $normalizer;

    private $manager;
    /** @var JWTManager */
    private $jwtManager;

    private static $instance;

    public function __construct(JWTManager $jwtManager, Sdk $sdk, iterable $config, iterable $credentials, string $env, iterable $snsConfig, ObjectNormalizer $normalizer, EntityManagerInterface $manager)
    {
        $this->client = $sdk->createSns($config + $credentials);
        $this->jwtManager = $jwtManager;

        $this->sdk = $sdk;
        $this->applicationName = BaseUtil::PROJECT_NAME.'_'.AppUtil::APP_NAME;
        $this->env = $env;
        $this->queuePrefix = $this->applicationName.'_'.$env.'_';
        $this->snsConfig = $snsConfig;
        $this->normalizer = $normalizer;
        $this->manager = $manager;

        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function fetchResource($resource, $queryParams = [])
    {
        $plurals = ['person' => 'people',
        ];

        $sadmin = new JWTUser('rootadmin', ['ROLE_SUPER_ADMIN',
        ]);

        $queryString = '';
        if (!empty($queryParams)) {
            $queryString = '?';
            $index = 0;
            foreach ($queryParams as $key => $val) {
                if ($index > 0) {
                    $queryString .= '&';
                    $index++;
                }
                $queryString .= $key.'='.$val;
            }
        }

        $token = $this->jwtManager->create($sadmin);

        $url = 'https://'.$_ENV[sprintf('%s_SERVICE_HOST', strtoupper($resource))].'/'.$plurals[$resource].$queryString;
        $client = new Client([
            'http_errors' => false,
            'verify' => false,
            'curl' => [
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]
        ]);

        try {
            $res = $client->request('GET', $url, ['headers' => ['Authorization' => $token]]);
            if ($res->getStatusCode() === 200) {
                $data = json_decode($res->getBody()->getContents(), true);
                return $data;
//                if (isset($data['hydra:totalItems']) && $data['hydra:totalItems'] > 0) {
//
//                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}