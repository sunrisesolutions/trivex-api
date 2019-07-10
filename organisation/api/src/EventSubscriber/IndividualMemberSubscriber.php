<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Attendee;
use App\Entity\Connection;
use App\Entity\IndividualMember;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Security\JWTUser;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class IndividualMemberSubscriber implements EventSubscriberInterface
{
    private $registry;
    private $mailer;
    private $security;

    public function __construct(RegistryInterface $registry, \Swift_Mailer $mailer, Security $security)
    {
        $this->registry = $registry;
        $this->mailer = $mailer;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        /** @var IndividualMember $member */
        $member = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$member instanceof IndividualMember || Request::METHOD_POST !== $method) {
            return;
        }

        /** @var JWTUser $user */
        $user = $this->security->getUser();
        if (empty($user) or empty($imUuid = $user->getImUuid())) {
            $event->setResponse(new JsonResponse(['Unauthorised access! Empty user or Member'], 401));
        }

        $imRepo = $this->registry->getRepository(IndividualMember::class);
        $im = $imRepo->findOneBy(['uuid' => $imUuid,
        ]);
//        $event->setResponse(new JsonResponse(['hello'=>'im','im'=>$im], 200));

        if (!empty($personUuid = $member->getPersonUuid())) {
            $person = $this->registry->getRepository(Person::class)->findOneBy(['uuid' => $personUuid]);
            if (empty($person)) {
                $person = new Person();
                $person->setUuid($personUuid);
            }
            $member->setPerson($person);
            $person->addIndividualMember($member);
        }

        if (!empty($orgUuid = $member->getOrganisationUuid())) {
            $org = $this->registry->getRepository(Organisation::class)->findOneBy(['uuid' => $orgUuid]);
            if (empty($org)) {
                throw new InvalidArgumentException('Invalid Organisation');
            }
            $member->setPerson($person);
            $member->setOrganisation($org);
            $person->addIndividualMember($member);
            $org->addIndividualMember($member);
        }

//        $event->setControllerResult($member);

//        throw new InvalidArgumentException('hello');

//        $event->setResponse(new JsonResponse(['attendee'=>$attendee->getRegistration()->getFamilyName(), 'user' => [
//            'im' => $user->getImUuid(),
//            'username' => $user->getUsername(), 'org' => $user->getOrgUuid()]], 200));
    }
}
