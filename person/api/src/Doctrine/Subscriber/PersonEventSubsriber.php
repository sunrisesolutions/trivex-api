<?php

namespace App\Doctrine\Subscriber;

use App\Entity\Person;
use App\Message\Message;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use App\Util\AwsSnsUtil;

class PersonEventSubsriber implements EventSubscriber {

    use RefreshDatabaseTrait;

    private $awsSnsUtil;

    public function __construct(AwsSnsUtil $awsSnsUtil) {
        $this->awsSnsUtil = $awsSnsUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(){
        return [
            'postPersist',
            'postUpdate',
            'postRemove'
        ];
    }

    public function postPersist(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if (!$object instanceof Person) {
            return;
        }
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_POST);
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if (!$object instanceof Person) {
            return;
        }
        $em = $args->getObjectManager();
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_PUT);
    }

    public function postRemove(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if (!$object instanceof Person) {
            return;
        }
        $em = $args->getObjectManager();
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_DELETE);
    }
}