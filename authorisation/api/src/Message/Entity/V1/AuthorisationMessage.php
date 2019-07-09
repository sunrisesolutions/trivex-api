<?php

namespace App\Message\Entity\V1;

use App\Entity\Organisation;
use App\Message\Entity\AuthorisationSupportedType;
use App\Message\Entity\OrganisationSupportedType;
use App\Message\Message;
use App\Util\AppUtil;
use Doctrine\ORM\EntityManagerInterface;

class AuthorisationMessage extends Message
{
    protected function getSupportedType(): string {
        return AuthorisationSupportedType::class;
    }

}