<?php

namespace App\Repository;

use App\Entity\TblProductData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TblProductData>
 *
 * @method TblProductData|null find($id, $lockMode = null, $lockVersion = null)
 * @method TblProductData|null findOneBy(array $criteria, array $orderBy = null)
 * @method TblProductData[]    findAll()
 * @method TblProductData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TblProductDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TblProductData::class);
    }

    //    /**
    //     * @return TblProductData[] Returns an array of TblProductData objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TblProductData
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
