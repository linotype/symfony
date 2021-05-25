<?php

namespace Linotype\Bundle\LinotypeBundle\Repository;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeTranslate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinotypeTranslate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinotypeTranslate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinotypeTranslate[]    findAll()
 * @method LinotypeTranslate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinotypeTranslateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinotypeTranslate::class);
    }


    public function findByTransId($values)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where( $qb->expr()->in( 'm.trans_id', $values ) );
        return $qb->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?LinotypeTranslate
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
