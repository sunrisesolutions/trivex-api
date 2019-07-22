<?php

namespace App\Doctrine\Subscriber;

use App\Entity\IndividualMember;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Entity\Role;
use App\Message\Message;
use App\Util\AppUtil;
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
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) return;

        $ar = [
            'data' => [
                'individualMember' => [
                    'uuid' => $object->getUuid(),
                    'accessToken' => $object->getAccessToken(),
                    'personUuid' => $object->getPersonUuid(),
                    'organisationUuid' => $object->getOrganisationUuid(),
                    '_SYSTEM_OPERATION' => Message::OPERATION_POST,
                ]
            ],
            'version' => AppUtil::MESSAGE_VERSION,
        ];

        foreach($object->getRoles() as $role) $ar['data']['individualMember']['role'][] = $role->getName();
        $ar['data']['individualMember']['role'] = json_encode($ar['data']['individualMember']['role']);

        return $this->awsSnsUtil->publishMessage(json_encode($ar), Message::OPERATION_POST);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof IndividualMember) {
            return;
        }
        $obj = new IndividualMember();
        $obj->setUuid($object->getUuid());
        return $this->awsSnsUtil->publishMessage($obj, Message::OPERATION_DELETE);
    }
}