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

    public function findLastUserScan(int $user_id)
    {
        $entityManagerConnexion = $this->getEntityManager()->getConnection();
        $sql = 'SELECT c.* FROM `checkpoint` c
                    INNER JOIN `scan_qr` s ON c.id = s.checkpoint_id
                    INNER JOIN `round` r ON r.id = s.round_id AND r.user_id = '.$user_id.'
                    INNER JOIN instance i ON i.id = r.instance_id AND (
                    (
                        i.start_at <= cast(now() as datetime)
                        AND i.end_at >= cast(now() as datetime)
                    )
                )
                WHERE r.end_at IS NOT NULL 
                ORDER BY s.scan_at DESC LIMIT 1;';
        $query = $entityManagerConnexion->executeQuery($sql); 
        return $query->fetchAssociative();
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
