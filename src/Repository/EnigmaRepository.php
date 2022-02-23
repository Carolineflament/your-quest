<?php

namespace App\Repository;

use App\Entity\Enigma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Enigma|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enigma|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enigma[]    findAll()
 * @method Enigma[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnigmaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enigma::class);
    }

    // /**
    //  * @return Enigma[] Returns an array of Enigma objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Enigma
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
