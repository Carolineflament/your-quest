<?php

namespace App\Repository;

use App\Entity\ScanQR;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ScanQR|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScanQR|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScanQR[]    findAll()
 * @method ScanQR[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScanQRRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScanQR::class);
    }

    // /**
    //  * @return ScanQR[] Returns an array of ScanQR objects
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
    public function findOneBySomeField($value): ?ScanQR
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
