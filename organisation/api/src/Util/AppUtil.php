<?php

namespace App\Util;

class AppUtil
{
    const APP_NAME = 'ORGANISATION';
    const SDK_VERSION = 'latest';

    public static function generateUuid($prefix = self::APP_NAME)
    {
        return sprintf('%s-%s-%s', $prefix, uniqid(), date_format(new \DateTime(), 'HidmY'));
    }
}