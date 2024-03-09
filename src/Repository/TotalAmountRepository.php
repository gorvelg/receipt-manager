<?php

namespace App\Repository;

use App\Entity\TotalAmount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TotalAmount>
 *
 * @method TotalAmount|null find($id, $lockMode = null, $lockVersion = null)
 * @method TotalAmount|null findOneBy(array $criteria, array $orderBy = null)
 * @method TotalAmount[]    findAll()
 * @method TotalAmount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TotalAmountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TotalAmount::class);
    }

    //    /**
    //     * @return TotalAmount[] Returns an array of TotalAmount objects
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

    //    public function findOneBySomeField($value): ?TotalAmount
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
