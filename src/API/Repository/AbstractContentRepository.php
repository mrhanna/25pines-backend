<?php

namespace App\API\Repository;

use App\API\Entity\AbstractContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
// use Doctrine\ORM\OptimisticLockException;
// use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbstractContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractContent[]    findAll()
 * @method AbstractContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbstractContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $fqcn = AbstractContent::class)
    {
        parent::__construct($registry, $fqcn);
    }

    // /**
    //  * @throws ORMException
    //  * @throws OptimisticLockException
    //  */
    /*
    public function add(AbstractContent $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    */

    // /**
    //  * @throws ORMException
    //  * @throws OptimisticLockException
    //  */
    /*
    public function remove(AbstractContent $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    */

    /**
     * @return Collection<AbstractContent>
     */
    public function findAllWithTag(string $tag): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.tags', 't')
            ->andWhere('t.name=:tag')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return AbstractContent[] Returns an array of AbstractContent objects
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
    public function findOneBySomeField($value): ?AbstractContent
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
