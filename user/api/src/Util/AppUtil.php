<?php

namespace App\Util;

use App\Message\Entity\OrganisationSupportedType;
use Doctrine\ORM\EntityManagerInterface;

class AppUtil extends BaseUtil
{
    const APP_NAME = 'USER';
    const MESSAGE_VERSION = 1;

    public static function getAppName()
    {
        return self::APP_NAME;
    }
}