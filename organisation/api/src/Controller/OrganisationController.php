<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Organisation;


class OrganisationController extends AbstractController
{
    /**
     * @Route("/organisation/logourl/{subdomain}", name="org_logo", requirements={"subdomain"="[a-zA-Z0-9\-_]+"})
     */
    public function getLogoUrl(Request $request, $subdomain)
    {
        $repo = $this->getDoctrine()->getRepository(Organisation::class);
        $org = $repo->findOneBy(['subdomain' => $subdomain]);
        if (empty($org) || empty($org->getLogoReadUrl())) {
            throw new NotFoundHttpException('Not Found');
        }
        return new JsonResponse(['logoReadUrl' => $org->getLogoReadUrl()]);
    }
}
