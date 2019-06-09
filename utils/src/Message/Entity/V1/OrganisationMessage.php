<?php

namespace App\Message\Entity\V1;

use App\Entity\Organisation;
use App\Message\Entity\OrganisationSupportedType;
use App\Message\Message;
use App\Util\AppUtil;
use Doctrine\ORM\EntityManagerInterface;

class OrganisationMessage extends Message
{
    public function updateEntity(EntityManagerInterface $manager)
    {
        $props = get_object_vars($this->data);
        foreach ($props as $prop => $obj) {
            echo 'prop is '.$prop.'  ';
            $orgSupportClass = OrganisationSupportedType::class;
            if (defined("$orgSupportClass::$prop")) {
                $className = constant("$orgSupportClass::$prop");
                $repo = $manager->getRepository($className);
                $entity = $repo->findOneBy(['uuid' => $obj->uuid]);
                $nonScalarProps = AppUtil::copyObjectScalarProperties($obj, $entity);

                foreach ($nonScalarProps as $_prop => $_obj) {
                    if (defined("$orgSupportClass::$_prop")) {
                        $_className = constant("$orgSupportClass::$_prop");
                        $_repo = $manager->getRepository($_className);
                        $_entity = $_repo->findOneBy(['uuid' => $_obj->uuid]);
                        echo '_prop is '.$_prop.' uuid: '.$_obj->uuid.'  '.$_className.' '.empty($_entity).'  ';
                        if (empty($_entity)) {
                            $_entity = new $_className;
                            $_entity->setUuid($_obj->uuid);
                        }
                        $setter = 'set'.ucfirst(strtolower($_prop));
                        $entity->{$setter}($_entity);
                        $manager->persist($_entity);
                    }
                }
                $manager->persist($entity);
            }
        }
        $manager->flush();
    }
}