<?php

namespace Linotype\Bundle\LinotypeBundle\Repository;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinotypeOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinotypeOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinotypeOption[]    findAll()
 * @method LinotypeOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinotypeOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinotypeOption::class);
    }

    // /**
    //  * @return LinotypeOption[] Returns an array of LinotypeOption objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LinotypeOption
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
