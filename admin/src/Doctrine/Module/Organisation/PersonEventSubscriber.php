<?php

namespace App\Doctrine\Module\Organisation;

use App\Doctrine\Module\ORMEventSubscriber;
use App\Entity\Organisation\Person;
use App\Entity\Organisation\Organisation;
use App\Entity\Organisation\Role;
use App\Message\Message;
use App\Util\Organisation\AppUtil;
use App\Util\Organisation\AwsSnsUtil;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

class PersonEventSubscriber implements ORMEventSubscriber
{

    private $awsSnsUtil;
    private $manager;
    function __construct(AwsSnsUtil $awsSnsUtil, EntityManagerInterface $manager)
    {
        $this->awsSnsUtil = $awsSnsUtil;
        $this->manager = $manager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    private function updateEntity(Person $object)
    {
        $person = $object;
        if (!empty($person->getUuid())) {
            return;
        }
        $email = $person->getEmail();
        $phone = $person->getPhoneNumber();
        $manager = $this->manager;
        $pRepo = $manager->getRepository(\App\Entity\Person\Person::class);
        /** @var \App\Entity\Person\Person $fPerson */
        $fPerson = $pRepo->findOneBy(['email' => $email,
        ]);
        if (empty($fPerson)) {
            $fPerson = $pRepo->findOneBy(['phoneNumber' => $phone,
            ]);
        }
        if (!empty($fPerson)) {
            AppUtil::copyObjectScalarProperties($fPerson, $person);
        } else {
            $fPerson = new \App\Entity\Person\Person();
            AppUtil::copyObjectScalarProperties($person, $fPerson);
            $manager->persist($fPerson);
            $manager->flush($fPerson);
            AppUtil::copyObjectScalarProperties($fPerson, $person);
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
        $this->updateEntity($object);
    }

    public function preUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
        $this->updateEntity($object);
    }


    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Person) {
            return;
        }
    }
}