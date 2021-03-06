<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Attendee;
use App\Entity\Connection;
use App\Entity\IndividualMember;
use App\Entity\Nationality;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\Role;
use App\Security\JWTUser;
use App\Util\AwsSnsUtil;
use App\Util\AwsSqsUtil;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use GuzzleHttp\Client;
use mysql_xdevapi\Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use App\Message\Message;
use App\Util\AppUtil;

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
        $c = Criteria::create()->andWhere(Criteria::expr()->eq('name', 'ROLE_ORG_ADMIN'));
        if ($member->admin === true) {
            $role = $member->getRoles()->matching($c)->first();
            if (empty($role)) {
                $role = new Role();
                $role->initiateUuid();
                $role->setName('ROLE_ORG_ADMIN');
                $role->addIndividualMember($member);
                $role->setOrganisation($member->getOrganisation());
                $manager->persist($role);
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
    }

    private function makeMessageAdmin(IndividualMember $member, ObjectManager $manager)
    {
        $c = Criteria::create()->andWhere(Criteria::expr()->eq('name', 'ROLE_MSG_ADMIN'));
        if ($member->admin === true) {
            $role = $member->getRoles()->matching($c)->first();
            if (empty($role)) {
                $role = new Role();
                $role->initiateUuid();
                $role->setName('ROLE_MSG_ADMIN');
                $role->addIndividualMember($member);
                $role->setOrganisation($member->getOrganisation());
                $manager->persist($role);
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
    }

    private function makeMessageUser(IndividualMember $member, ObjectManager $manager)
    {
        $c = Criteria::create()->andWhere(Criteria::expr()->eq('name', 'ROLE_MSG_USER'));
        if ($member->messageDeliverable === true) {
            $role = $member->getRoles()->matching($c)->first();
            if (empty($role)) {
                $role = new Role();
                $role->initiateUuid();
                $role->setName('ROLE_MSG_USER');
                $role->addIndividualMember($member);
                $role->setOrganisation($member->getOrganisation());
                $manager->persist($role);
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

        $orgUuid = $member->getOrganisationUuid();
        $personUuid = $member->getPersonUuid();

        if (empty($orgUuid) or empty($personUuid)) {
            return;
        }

        if (!empty($orgUuid)) {
            $org = $this->registry->getRepository(Organisation::class)->findOneBy(['uuid' => $orgUuid]);
        }

        if (empty($org)) {
            throw new InvalidArgumentException('Invalid Organisation');
        }

        if (!empty($personUuid)) {
            $person = $this->registry->getRepository(Person::class)->findOneBy(['uuid' => $personUuid]);
            if (empty($person)) {
                $person = new Person();
                $person->setUuid($personUuid);

                $token = $event->getRequest()->headers->get('Authorization');
                $url = 'https://' . $_ENV['PERSON_SERVICE_HOST'] . '/people?uuid=' . $personUuid;
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
                        if (isset($data['hydra:totalItems']) && $data['hydra:totalItems'] > 0) {
                            $person->setGivenName($data['hydra:member'][0]['givenName'] ?? null);
                            $person->setJobTitle($data['hydra:member'][0]['jobTitle'] ?? null);
                            $person->setBirthDate($data['hydra:member'][0]['birthDate'] ? new \DateTime($data['hydra:member'][0]['birthDate']) : null);
                            $person->setEmail($data['hydra:member'][0]['email'] ?? null);
                            $person->setPhoneNumber($data['hydra:member'][0]['phoneNumber'] ?? null);
                        }
                    }
                } catch (\Exception $exception) {}
                $this->manager->persist($person);
            }
        }

        if (empty($person)) {
            throw new InvalidArgumentException('Invalid Person');
        }

        if ($method === Request::METHOD_POST && !empty($person->getId())) {
            $im = $this->registry->getRepository(IndividualMember::class)->findOneBy(['organisation' => $org->getId(), 'person' => $person->getId()]);
            if (!empty($im)) $event->setResponse(new JsonResponse(['Member already exist'], 400));
        }

        $person->setEmployerName($org->getName());
        $person->addIndividualMember($member);
        $member->setPerson($person);
        $org->addIndividualMember($member);
        $member->setOrganisation($org);
        $this->makeAdmin($member, $this->manager);
        $this->makeMessageAdmin($member, $this->manager);
        $this->makeMessageUser($member, $this->manager);

//        if ($member->admin != $member->hasRole('ROLE_ORG_ADMIN')) $this->memberRole($member, 'ROLE_ORG_ADMIN');
//        if ($member->messageAdmin != $member->hasRole('ROLE_MSG_ADMIN')) $this->memberRole($member, 'ROLE_MSG_ADMIN');
//        if ($member->messageDeliverable != $member->hasRole('ROLE_MSG_USER')) $this->memberRole($member, 'ROLE_MSG_USER');

        //publishMessage
        if ($method === Request::METHOD_PUT) {
            $ar = [
                'data' => [
                    'individualMember' => [
                        'uuid' => $member->getUuid(),
                        'accessToken' => $member->getAccessToken(),
                        'personUuid' => $member->getPersonUuid(),
                        'organisationUuid' => $member->getOrganisationUuid(),
                        '_SYSTEM_OPERATION' => Message::OPERATION_PUT,
                    ]
                ],
                'version' => AppUtil::MESSAGE_VERSION,
            ];

            $names = [];
            foreach($member->getRoles() as $role) $names[] = $role->getName();
            $ar['data']['individualMember']['roleString'] = json_encode($names);
            $this->awsSnsUtil->publishMessage(json_encode($ar), $method);
        }
    }

    private function memberRole(IndividualMember $member, string $roleName) {
        if ($member->hasRole($roleName)) {
            $c = Criteria::create()->where(Criteria::expr()->eq('name', $roleName));
            $role = $member->getRoles()->matching($c)->first();
            $member->removeRole($role);
        } else {
            $role = new Role();
            $role->initiateUuid();
            $role->setName($roleName);
            $role->addIndividualMember($member);
            $role->setOrganisation($member->getOrganisation());
            $this->manager->persist($role);
            $member->addRole($role);
        }
    }
}
