<?php

namespace App\Util;

use App\Message\Entity\OrganisationSupportedType;
use Doctrine\ORM\EntityManagerInterface;

class AppUtil extends BaseUtil
{
    const APP_NAME = 'CONFIG';

    public static function getAppName()
    {
        return self::APP_NAME;
    }
}