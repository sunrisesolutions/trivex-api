<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Attendee;
use App\Entity\Connection;
use App\Entity\IndividualMember;
use App\Security\JWTUser;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class ConnectionSubscriber implements EventSubscriberInterface
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
        /** @var Connection $connection */
        $connection = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$connection instanceof Connection || Request::METHOD_POST !== $method) {
            return;
        }

        /** @var JWTUser $user */
//        $user = $this->security->getUser();
//        if (empty($user) or empty($imUuid = $user->getImUuid())) {
//            $event->setResponse(new JsonResponse(['Unauthorised access! Empty user or Member'], 401));
//        }

        $imUuid = 'ORG_IM-2';

        $imRepo = $this->registry->getRepository(IndividualMember::class);
        $im = $imRepo->findOneBy(['uuid' => $imUuid,
        ]);
//        $event->setResponse(new JsonResponse(['hello'=>'im','im'=>$im], 200));

        $connection->setFromMember($im);

//        $event->setControllerResult($connection);

//        throw new InvalidArgumentException('hello');

//        $event->setResponse(new JsonResponse(['attendee'=>$attendee->getRegistration()->getFamilyName(), 'user' => [
//            'im' => $user->getImUuid(),
//            'username' => $user->getUsername(), 'org' => $user->getOrgUuid()]], 200));
    }
}
