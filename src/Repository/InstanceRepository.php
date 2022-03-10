<?php

namespace App\Repository;

use App\Entity\Instance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Instance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instance[]    findAll()
 * @method Instance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Instance::class);
    }

    public function findBySlugWithId(string $slug, int $id)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.slug = :slug AND i.id != :id')
            ->setParameter('slug', $slug)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findBySlugWithoutId(string $slug)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Instance[] Returns an array of Instance objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Instance
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
