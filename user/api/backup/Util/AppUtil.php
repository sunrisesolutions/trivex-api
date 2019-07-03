<?php

namespace App\Util;

use App\Message\Entity\OrganisationSupportedType;
use Doctrine\ORM\EntityManagerInterface;

class AppUtil extends BaseUtil
{
    const APP_NAME = 'USER';
    const PROJECT_NAME = 'TRIVEX';

    public static function getFullAppName($name)
    {
        $names = ['ORG' => 'Organisation',
        ];
        return $names[$name];
    }

    public static function generateUuid($prefix = self::APP_NAME)
    {
        return sprintf('%s-%s-%s', $prefix, uniqid(), date_format(new \DateTime(), 'HidmY'));
    }


}