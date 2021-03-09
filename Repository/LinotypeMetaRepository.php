<?php

namespace Linotype\SymfonyBundle\Repository;

use Linotype\SymfonyBundle\Entity\LinotypeMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinotypeMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinotypeMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinotypeMeta[]    findAll()
 * @method LinotypeMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinotypeMetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinotypeMeta::class);
    }


    public function findByKeys($values)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where( $qb->expr()->in( 'm.context_key', $values ) );
        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return LinotypeMeta[] Returns an array of LinotypeMeta objects
    //  */
    
    public function findByTemplateId($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.template_id = :val')
            ->setParameter('val', $value)
            // ->orderBy('l.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?LinotypeMeta
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
