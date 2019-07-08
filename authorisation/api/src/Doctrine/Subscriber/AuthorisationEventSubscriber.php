<?php

namespace App\Doctrine\Subscriber;

use App\Entity\Organisation;
use App\Message\Message;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Util\AwsSnsUtil;

class AuthorisationEventSubscriber implements EventSubscriber {

    private $awsSnsUtil;

    public function __construct(AwsSnsUtil $awsSnsUtil) {
        $this->awsSnsUtil = $awsSnsUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(){
        return [
            'prePersist',
        ];
    }

    public function prePersist(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if (!$object instanceof Organisation) {
            return;
        }
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_POST);
    }
}