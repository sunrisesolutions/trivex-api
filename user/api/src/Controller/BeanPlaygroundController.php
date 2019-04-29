<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/bean", condition="'%kernel.environment%' === 'dev'")
 */
class BeanPlaygroundController
{
    /**
     * @Route("/hello", methods="GET")
     */
    public function publishMessage(Request $request): Response
    {
//        $content = json_decode($request->getContent(), true);

        return new JsonResponse(['hello honey']);
    }
}
