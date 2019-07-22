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


//        $imRepo = $this->registry->getRepository(IndividualMember::class);
//        $im = $imRepo->findOneBy(['uuid' => $imUuid,]);

//        $event->setResponse(new JsonResponse(['hello'=>'im','im'=>$im], 200));

        if (!empty($orgUuid = $member->getOrganisationUuid())) {
            $org = $this->registry->getRepository(Organisation::class)->findOneBy(['uuid' => $orgUuid]);
            if (empty($org)) {
                throw new InvalidArgumentException('Invalid Organisation');
            }

            if (empty($personUuid = $member->getPersonUuid())) {
                throw new InvalidArgumentException('Invalid Person');
            }

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
        }


//        $event->setControllerResult($member);

//        throw new InvalidArgumentException('hello');

//        $event->setResponse(new JsonResponse(['attendee'=>$attendee->getRegistration()->getFamilyName(), 'user' => [
//            'im' => $user->getImUuid(),
//            'username' => $user->getUsername(), 'org' => $user->getOrgUuid()]], 200));
    }
}
