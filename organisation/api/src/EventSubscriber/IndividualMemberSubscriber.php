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
use App\Util\AwsSnsUtil;
use App\Util\AwsSqsUtil;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class IndividualMemberSubscriber implements EventSubscriberInterface
{
    private $registry;
    private $mailer;
    private $security;
    private $manager;
    private $awsSnsUtil;

    public function __construct(RegistryInterface $registry, \Swift_Mailer $mailer, Security $security,EntityManagerInterface $manager, AwsSnsUtil $awsSnsUtil)
    {
        $this->registry = $registry;
        $this->mailer = $mailer;
        $this->security = $security;
        $this->manager = $manager;
        $this->awsSnsUtil = $awsSnsUtil;
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
                $manager->persist($role);
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


//        $imRepo = $this->registry->getRepository(IndividualMember::class);
//        $im = $imRepo->findOneBy(['uuid' => $imUuid,]);

//        $event->setResponse(new JsonResponse(['hello'=>'im','im'=>$im], 200));

//        if (!empty($personUuid = $member->getPersonUuid())) {
//            $person = $this->registry->getRepository(Person::class)->findOneBy(['uuid' => $personUuid]);
//            if (empty($person)) {
//                $person = new Person();
//                $person->setUuid($personUuid);
//            }
//            $member->setPerson($person);
//            $person->addIndividualMember($member);
//        }
//
//        if (!empty($orgUuid = $member->getOrganisationUuid())) {
//            $org = $this->registry->getRepository(Organisation::class)->findOneBy(['uuid' => $orgUuid]);
//            if (empty($org)) {
//                throw new InvalidArgumentException('Invalid Organisation');
//            }
//            $member->setPerson($person);
//            $member->setOrganisation($org);
//            $person->addIndividualMember($member);
//            $org->addIndividualMember($member);
//        }

        //$this->makeAdmin($member, $this->manager);

//        $event->setControllerResult($member);

//        throw new InvalidArgumentException('hello');

//        $event->setResponse(new JsonResponse(['attendee'=>$attendee->getRegistration()->getFamilyName(), 'user' => [
//            'im' => $user->getImUuid(),
//            'username' => $user->getUsername(), 'org' => $user->getOrgUuid()]], 200));

        if (!empty($orgUuid = $member->getOrganisationUuid())) {
            $org = $this->registry->getRepository(Organisation::class)->findOneBy(['uuid' => $orgUuid]);
            if (empty($org)) throw new InvalidArgumentException('Invalid Organisation');

            if (!empty($personUuid = $member->getPersonUuid())) {
                $person = $this->registry->getRepository(Person::class)->findOneBy(['uuid' => $personUuid]);
                if (empty($person)) {
                    $person = new Person();
                    $person->setUuid($personUuid);
                    $this->manager->persist($person);
                }
            } else throw new InvalidArgumentException('Invalid Person');

            $im = $this->registry->getRepository(IndividualMember::class)->findOneBy(['organisation' => $org->getId(), 'person' => $person->getId()]);
            if (!empty($im)) $this->manager->remove($im);

            $member->setPerson($person);
            $member->setOrganisation($org);

            //makeAdmin
            $c = Criteria::create()->andWhere(Criteria::expr()->eq('name', 'ROLE_ORG_ADMIN'));

            if ($member->admin === true) {
                $role = $member->getRoles()->matching($c)->first();
                if (empty($role)) {
                    $role = new Role();
                    $role->initiateUuid();
                    $role->setName('ROLE_ORG_ADMIN');
                    $role->setOrganisation($org);
                    $role->setIndividualMember($member);
                    $this->manager->persist($role);
                }
                $member->addRole($role);
            } else {
                $roles = $member->getRoles()->matching($c);
                if ($roles->count() > 0) {
                    foreach ($roles as $role) {
                        $member->removeRole($role);
                    }
                }
            }

            //publishMessage
            $object = clone $member;
            $object->setPerson(null);
            $object->setOrganisation(null);
            $this->awsSnsUtil->publishMessage($object, $method);
        }
    }
}
