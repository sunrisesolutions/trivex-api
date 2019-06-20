<?php

namespace App\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\IndividualMember;
use App\Security\JWTUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

final class CurrentOrganisationExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        /** @var JWTUser $user */
        $user = $this->security->getUser();
        if (empty($user)) {
            throw new UnauthorizedHttpException('Please login');
        }

        if (!$this->supportClass($resourceClass) || $this->security->isGranted('ROLE_ADMIN') || null === $objectUuid = $user->getOrgUuid()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->join($rootAlias.'.organisation', 'organisation');
        $queryBuilder->andWhere('organisation.uuid like :current_object');
        $queryBuilder->setParameter('current_object', $objectUuid);

//        echo $queryBuilder->getQuery()->getSQL();
    }

    private function supportClass($class)
    {
        return in_array($class, [IndividualMember::class]);
    }
}