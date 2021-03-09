<?php

namespace Linotype\Bundle\Repository;

use Linotype\Bundle\Entity\LinotypeTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinotypeTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinotypeTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinotypeTemplate[]    findAll()
 * @method LinotypeTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinotypeTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinotypeTemplate::class);
    }

    // /**
    //  * @return LinotypeTemplate[] Returns an array of LinotypeTemplate objects
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
    public function findOneBySomeField($value): ?LinotypeTemplate
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
