<?php

namespace App\Util;

use App\Message\Entity\OrganisationSupportedType;
use Doctrine\ORM\EntityManagerInterface;

class BaseUtil
{
    const PROJECT_NAME = 'TRIVEX';

    public static function getFullAppName($name)
    {
        $names = [
            'ORG' => 'Organisation',
            'PERSON' => 'Person'
        ];
        return $names[$name];
    }

    public static function generateUuid($prefix = AppUtil::APP_NAME)
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

            echo 'prop is ' . $prop . '  ';
            if (is_scalar($val)) {
                $setter = 'set' . ucfirst(strtolower($prop));
                $dest->{$setter}($val);
            } else {
                $nonScalarProps[$prop] = $val;
            }
        }
        return $nonScalarProps;
    }

}