<?php
/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine\Subscriber;

use App\Entity\OrganisationUser;
use App\Entity\Organisation;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class OrganisationUserEventSubsriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postUpdate];
    }

    public function postPersist(LifecycleEventArgs $args) {
        $object = $args->getObject();
        $em = $args->getObjectManager();
        if (!$object instanceof OrganisationUser) return;
        if (!empty($object->getOrganisationUuid())) {
            $org = $em->getRepository(Organisation::class)->findOneBy(['uuid' => $object->getOrganisationUuid()]);
            if (!empty($org)) {
                $org->addOrganisationUser($object);
                $object->setOrganisation($org);
                $em->persist($org);
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();
        $em = $args->getObjectManager();
        if (!$object instanceof OrganisationUser) return;
        if (!empty($object->getOrganisationUuid())) {
            $org = $em->getRepository(Organisation::class)->findOneBy(['uuid' => $object->getOrganisationUuid()]);
            if (!empty($org)) {
                $org->addOrganisationUser($object);
                $object->setOrganisation($org);
                $em->persist($org);
            }
        }
    }
}
