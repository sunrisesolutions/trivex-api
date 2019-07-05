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
//        $props = get_object_vars($source);
        $reflection = new \ReflectionClass($source);
        $reflectionProps = $reflection->getProperties();

        $nonScalarProps = [];
        /** @var \ReflectionProperty $reflectionProp */
        foreach ($reflectionProps as $reflectionProp) {
            $prop = $reflectionProp->getName();

            if ($prop === 'id') {
                continue;
            }

            $getter = 'get' . ucfirst(strtolower($prop));
            $val = $source->{$getter}();
            if (is_scalar($val) || $val instanceof \DateTime) {
                $setter = 'set' . ucfirst(strtolower($prop));
                $dest->{$setter}($val);
            } elseif ($val !== null) {
                $nonScalarProps[$prop] = $val;
            }
        }
        return $nonScalarProps;
    }
}