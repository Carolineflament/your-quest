<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findBySlugAndId(string $slug, int $id)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.slug = :slug AND g.id != :id')
            ->setParameter('slug', $slug)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNextGame()
    {
        $entityManagerConnexion = $this->getEntityManager()->getConnection();
        $sql = 'SELECT g.id FROM `game` g 
                    INNER JOIN instance i ON i.game_id = g.id AND (
                    (
                        (
                            i.start_at <= cast(now() as date) OR 
                            (
                                i.start_at > cast(now() as date) 
                                AND i.start_at < cast((now() + interval 30 day) as date)
                            )
                        )
                        AND i.end_at >= cast(now() as date)
                    )
                )
                WHERE g.is_trashed = 0 AND g.status = 1 GROUP BY g.id;';
        $query = $entityManagerConnexion->executeQuery($sql); 
        $results = $query->fetchAllAssociative();
        $ids = array();
        foreach($results AS $result)
        {
            $ids[] = $result['id'];
        }
        
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('SELECT g FROM App\Entity\Game g WHERE g.id IN('.implode(',', $ids).')');
        return $query->getResult();
    }

    // /**
    //  * @return Game[] Returns an array of Game objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Game
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
