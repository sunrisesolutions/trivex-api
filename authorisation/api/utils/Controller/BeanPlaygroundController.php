<?php

namespace App\Controller;

use App\Service\AppService;
use App\Util\AwsSnsUtil;
use App\Util\AwsSqsUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BeanPlaygroundController extends AbstractController
{
    private $service;
    private $sqsUtil;
    private $snsUtil;

    public function __construct(AppService $service, AwsSqsUtil $sqsUtil, AwsSnsUtil $snsUtil)
    {
        $this->service = $service;
        $this->sqsUtil = $sqsUtil;
        $this->snsUtil = $snsUtil;
    }

    /**
     * @Route("/bean/playground", name="bean_playground")
     */
    public function index()
    {
//        $r = $this->service->listTopics();
//        $r = $this->service->getTopicArns();
        $r = $this->service->initiateTopics();
//        $r = $this->sqsUtil->listQueues();
//        $r = $this->snsUtil->listSubscriptionsByTopic('arn:aws:sns:ap-southeast-1:073853278715:TRIVEX_ORG_DEV');

        return $this->json([
            'r' => $r,
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BeanPlaygroundController.php',
        ]);
    }
}

