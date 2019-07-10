<?php

namespace App\Message\Entity\V1;

use App\Message\Entity\AcroleSupportedType;
use App\Message\Message;
use Doctrine\ORM\EntityManagerInterface;

class AcroleMessage extends Message
{
    protected function getSupportedType(): string {
        return AcroleSupportedType::class;
    }

    protected function prePersist($obj, $entity)
    {
        parent::prePersist($obj, $entity);
        $entity->organisationUuid = $obj->organisationUuid;
    }
}