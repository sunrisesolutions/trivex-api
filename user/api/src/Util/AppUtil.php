<?php

namespace App\Util;

class AppUtil
{
    const APP_NAME = 'USER';
    const PROJECT_NAME = 'TRIVEX';
    const TOPIC_ARN = 'arn:aws:sns:ap-southeast-1:073853278715:sample-topic-1';

    public static function generateUuid($prefix = self::APP_NAME){
        return sprintf('%s-%s-%s',$prefix, uniqid(),date_format(new \DateTime(),'HidmY'));
    }
}