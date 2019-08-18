<?php
// api/src/Filter/RegexpFilter.php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Delivery;
use Doctrine\ORM\QueryBuilder;

final class NotLikeFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }
        $expr = $queryBuilder->expr();
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if ($resourceClass === Delivery::class) {
            if (!empty($value)) {
                $queryBuilder->join('message.sender', 'messageSender');
                $queryBuilder->andWhere($expr->notLike('messageSender.uuid', $expr->literal($value)));
            }
        }

//        $parameterName = $queryNameGenerator->generateParameterName($property); // Generate a unique parameter name to avoid collisions with other filters
//        $expr = $queryBuilder->expr();
//        if ($property === 'messageSenderUuid') {
////            $queryBuilder->join('o.message', 'message')->join('message.sender', 'messageSender');
////            $queryBuilder->andWhere($expr->notLike('messageSender.uuid', $expr->literal($value)));
//        } else {
//            $queryBuilder
//                ->andWhere($expr->notLike('o.'.$property, $parameterName))
////            ->andWhere(sprintf('REGEXP(o.%s, :%s) = 1', $property, $parameterName))
//                ->setParameter($parameterName, $value);
//        }
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        if ($resourceClass === Delivery::class) {
            $description["not_like_messageSenderUuid"] = [
                'property' => 'messageSenderUuid',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter Sender UUID of a delivery using a NOT LIKE operator.',
                    'name' => 'Message Sender UUID',
                    'type' => 'Will appear below the name in the Swagger documentation',
                ],
            ];
        }

        if (!$this->properties) {
            return $description;
//            return [];
        }

        foreach ($this->properties as $property => $strategy) {
            $description["not_like_$property"] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter using a NOT LIKE operator. This will appear in the Swagger documentation!',
                    'name' => 'Not-Like Filter',
                    'type' => 'Will appear below the name in the Swagger documentation',
                ],
            ];
        }


//        $description["not_like_message_sender_uuid"] = [
//            'property' => 'messageSenderUuid',
//            'type' => 'string',
//            'required' => false,
//            'swagger' => [
//                'description' => 'Filter using a NOT LIKE operator. This will appear in the Swagger documentation!',
//                'name' => 'Not-Like Filter',
//                'type' => 'Will appear below the name in the Swagger documentation',
//            ],
//        ];

        return $description;
    }
}
