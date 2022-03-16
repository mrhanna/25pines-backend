<?php

namespace App\API\Repository;

use App\API\Entity\AbstractStreamableContent;
use App\API\Repository\AbstractContentRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbstractStreamableContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractStreamableContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractStreamableContent[]    findAll()
 * @method AbstractStreamableContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbstractStreamableContentRepository extends AbstractContentRepository
{
    public function __construct(ManagerRegistry $registry, string $fqcn = AbstractStreamableContent::class)
    {
        parent::__construct($registry, $fqcn);
    }

    // /**
    //  * @throws ORMException
    //  * @throws OptimisticLockException
    //  */
    /*
    public function add(StreamableAbstractContent $entity, bool $flush = true): void
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
    public function remove(StreamableAbstractContent $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    */

    // /**
    //  * @return AbstractStreamableContent[] Returns an array of AbstractStreamableContent objects
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
    public function findOneBySomeField($value): ?AbstractStreamableContent
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
