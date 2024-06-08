<?php

namespace App\Repository;

use App\Entity\LeaveBalance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeaveBalance>
 *
 * @method LeaveBalance|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaveBalance|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaveBalance[]    findAll()
 * @method LeaveBalance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaveBalanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaveBalance::class);
    }

//    /**
//     * @return LeaveBalance[] Returns an array of LeaveBalance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LeaveBalance
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
