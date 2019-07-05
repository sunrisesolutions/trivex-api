<?php

namespace App\Message\Entity\V1;

use App\Entity\Organisation;
use App\Message\Entity\PersonSupportedType;
use App\Message\Message;
use App\Util\AppUtil;
use Doctrine\ORM\EntityManagerInterface;

class PersonMessage extends Message
{
    protected function getSupportedType(){
        return PersonSupportedType::class;
    }

}