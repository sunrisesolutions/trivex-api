<?php

namespace App\Doctrine\Module\Organisation;

use App\Doctrine\Module\ORMEventSubscriber;
use App\Entity\Organisation\Person;
use App\Entity\Organisation\Organisation;
use App\Entity\Organisation\Role;
use App\Entity\User\User;
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
            $manager->flush();
            AppUtil::copyObjectScalarProperties($fPerson, $person);
        }

        $upRepo = $manager->getRepository(\App\Entity\User\Person::class);
        /** @var \App\Entity\User\Person $fuPerson */
        $fuPerson = $upRepo->findOneBy(['email' => $email,
        ]);
        if (empty($fuPerson)) {
            $fuPerson = $upRepo->findOneBy(['phoneNumber' => $phone,
            ]);
        }
        if (empty($fuPerson)) {
            $fuPerson = new \App\Entity\User\Person();
            AppUtil::copyObjectScalarProperties($person, $fuPerson);
            $manager->persist($fuPerson);
            $manager->flush();
        }

        if (!empty($plainPassword = $person->getPassword()) && !empty($person->getEmail())) {
            if (empty($user = $fuPerson->getUser())) {
                $fuPerson = $this->manager->getRepository(\App\Entity\User\Person::class)->findOneBy(['uuid' => $person->getUuid()]);
                $user = $fuPerson->getUser();
                if (empty($user)) {
                    $user = new  User();
                    $user->setEmail($email);
                    $user->setUsername($email);
                    $fuPerson->setUser($user);
                }
                $user->setPlainPassword($plainPassword);
                $manager->persist($user);
                $manager->flush();

                $fPerson->setUserUuid($user->getUuid());
                $manager->persist($fPerson);
                $manager->flush();
            };
        }
        $object->setPassword(null);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
        $this->updateEntity($object);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
        $this->updateEntity($object);
    }


    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Person) return;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
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