<?php

namespace App\Controller;

use App\Util\AwsSnsUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BeanPlaygroundController extends AbstractController
{
    private $snsUtil;
    public function __construct(AwsSnsUtil $snsUtil)
    {
        $this->snsUtil = $snsUtil;
    }

    /**
     * @Route("/bean/playground", name="bean_playground")
     */
    public function index()
    {
        $this->snsUtil->createTopic('TEST_FROM_APP');
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BeanPlaygroundController.php',
        ]);
    }
}
