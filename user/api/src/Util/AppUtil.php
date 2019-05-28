<?php

namespace App\Util;

use App\Message\Entity\OrganisationSupportedType;
use Doctrine\ORM\EntityManagerInterface;

class AppUtil
{
    const APP_NAME = 'USER';
    const PROJECT_NAME = 'TRIVEX';
    const TOPIC_ARN = 'arn:aws:sns:ap-southeast-1:073853278715:TRIVEX_USER_PROD';

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

    public static function copyObjectScalarProperties($source, $dest)
    {
        $props = get_object_vars($source);
        $nonScalarProps = [];
        foreach ($props as $prop => $val) {
            if ($prop === 'id') {
                continue;
            }

            echo 'prop is '.$prop.'  ';
            if (is_scalar($val)) {
                $setter = 'set'.ucfirst(strtolower($prop));
                $dest->{$setter}($val);
            } else {
                $nonScalarProps[$prop] = $val;
            }
        }
        return $nonScalarProps;
    }
}