<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Attendee;
use App\Entity\Connection;
use App\Entity\IndividualMember;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\Role;
use App\Security\JWTUser;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
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
    private $manager;

    public function __construct(RegistryInterface $registry, \Swift_Mailer $mailer, Security $security,EntityManagerInterface $manager)
    {
        $this->registry = $registry;
        $this->mailer = $mailer;
        $this->security = $security;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }

    private function makeAdmin(IndividualMember $member, ObjectManager $manager)
    {
        if ($member->admin === true) {
            $c = Criteria::create();
            $expr = Criteria::expr();
            $c->andWhere($expr->eq('name', 'ROLE_ORG_ADMIN'));
            $role = $member->getRoles()->matching($c)->first();
            if (empty($role)) {
                $role = new Role();
                $role->initiateUuid();
                $role->setName('ROLE_ORG_ADMIN');
                $role->setOrganisation($member->getOrganisation());
                $member->getOrganisation()->addRole($role);
            }
            $member->addRole($role);
        } elseif ($member->admin === false) {
            $c = Criteria::create();
            $expr = Criteria::expr();
            $c->andWhere($expr->eq('name', 'ROLE_ORG_ADMIN'));
            $roles = $member->getRoles()->matching($c);
            if ($roles->count() > 0) {
                foreach ($roles as $role) {
                    $member->removeRole($role);
                    $manager->persist($role);
                }
            }

        }
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        /** @var IndividualMember $member */
        $member = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$member instanceof IndividualMember || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        /** @var JWTUser $user */
        $user = $this->security->getUser();
        if (empty($user) or (empty($imUuid = $user->getImUuid()) and !in_array('ROLE_ADMIN', $user->getRoles()))) {
            $event->setResponse(new JsonResponse(['Unauthorised access! Empty user or Member'], 401));
        }

        $this->makeAdmin($member, $this->manager);


//        $imRepo = $this->registry->getRepository(IndividualMember::class);
//        $im = $imRepo->findOneBy(['uuid' => $imUuid,]);

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
