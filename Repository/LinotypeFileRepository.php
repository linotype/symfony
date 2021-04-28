<?php

namespace Linotype\Bundle\LinotypeBundle\Repository;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinotypeFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinotypeFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinotypeFile[]    findAll()
 * @method LinotypeFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinotypeFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinotypeFile::class);
    }

}
