<?php

namespace App\Repository;

use App\Entity\Checkpoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Checkpoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Checkpoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method Checkpoint[]    findAll()
 * @method Checkpoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckpointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Checkpoint::class);
    }

    // /**
    //  * @return Checkpoint[] Returns an array of Checkpoint objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Checkpoint
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
