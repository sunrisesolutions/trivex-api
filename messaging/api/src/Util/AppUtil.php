<?php

namespace App\Util;

class AppUtil
{
    const APP_NAME = 'MSG';
    const BATCH_SIZE = 1000;

    public static function generateUuid($prefix = self::APP_NAME)
    {
        return sprintf('%s-%s-%s', $prefix, uniqid(), date_format(new \DateTime(), 'HidmY'));
    }
}