<?php

namespace App\Doctrine\Subscriber;

use App\Entity\IndividualMember;
use App\Entity\Role;
use App\Message\Message;
use App\Util\AwsSnsUtil;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Events;

class IndividualMemberEventSubscriber implements EventSubscriber
{

    private $awsSnsUtil;

    function __construct(AwsSnsUtil $awsSnsUtil)
    {
        $this->awsSnsUtil = $awsSnsUtil;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
            Events::prePersist,
            Events::preUpdate
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) {
            return;
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) {
            return;
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) {
            return;
        }
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_POST);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) {
            return;
        }
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_PUT);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) {
            return;
        }
        return $this->awsSnsUtil->publishMessage($object, Message::OPERATION_DELETE);
    }
}