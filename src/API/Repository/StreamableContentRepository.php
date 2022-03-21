<?php

namespace App\API\Repository;

use App\API\Entity\StreamableContent;
// use Doctrine\ORM\OptimisticLockException;
// use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StreamableContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method StreamableContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method StreamableContent[]    findAll()
 * @method StreamableContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamableContentRepository extends AbstractStreamableContentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StreamableContent::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(StreamableContent $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(StreamableContent $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return StreamableContent[] Returns an array of StreamableContent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StreamableContent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
