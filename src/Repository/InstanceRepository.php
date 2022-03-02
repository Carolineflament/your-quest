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

    /**
     * Find the next instance of a game that hasn't ended yet
     * 
     * @param int game_id The ID of the game we're looking for instances of.
     * 
     * @return An array of instances.
     */
    public function findNextInstance(int $game_id)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.endAt > :date AND i.isTrashed != :trash AND i.game = :game')
            ->setParameter('date', new \DateTimeImmutable())
            ->setParameter('trash', 1)
            ->setParameter('game', $game_id)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * FindPreviousInstance()
     * 
     * The function takes in a game_id and returns the previous instance of the game
     * 
     * @param int game_id The id of the game we're looking for.
     * 
     * @return An array of instances.
     */
    public function findPreviousInstance(int $game_id)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.endAt <= :date AND i.isTrashed != :trash AND i.game = :game')
            ->setParameter('date', new \DateTimeImmutable())
            ->setParameter('trash', 1)
            ->setParameter('game', $game_id)
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
