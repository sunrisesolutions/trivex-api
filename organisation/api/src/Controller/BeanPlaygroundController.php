<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Repository\OrganisationRepository;
use App\Util\AwsS3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/bean", condition="'%kernel.environment%' === 'dev'")
 */
class BeanPlaygroundController extends AbstractController
{
    /**
     * @Route("/hello", methods="GET")
     */
    public function hello(Request $request): Response
    {
        /** @var OrganisationRepository $repo */
        $repo = $this->container->get('doctrine')->getRepository(Organisation::class);
        $org = $repo->find(1);
        $org->setLogoName('new-name.jpg');

        return new JsonResponse(['hello honey']);
    }
}
