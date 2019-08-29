<?php

namespace App\Controller;

use App\Entity\IndividualMember;
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

    /**
     * @Route("/organisation/member-id-by-uuid/{uuid}", name="member_id_by_uuid")
     */
    public function getMemberIdByUuid(Request $request, $uuid)
    {
        $repo = $this->getDoctrine()->getRepository(IndividualMember::class);
        $member = $repo->findOneBy(['uuid' => $uuid]);
        if (empty($member)) {
            throw new NotFoundHttpException('Not Found');
        }
        return new JsonResponse(['memberId' => $member->getId()]);
    }
}
