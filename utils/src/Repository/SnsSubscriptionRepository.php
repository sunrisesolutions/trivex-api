<?php

namespace App\Repository;

use App\Entity\SnsSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SnsSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method SnsSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method SnsSubscription[]    findAll()
 * @method SnsSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SnsSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SnsSubscription::class);
    }

    // /**
    //  * @return SnsSubscription[] Returns an array of SnsSubscription objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SnsSubscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
