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

    public function save(LeaveBalance $leaveBalance): LeaveBalance
    {
        if ($leaveBalance->getId() === null) {
            $this->_em->persist($leaveBalance);
            $this->_em->flush();
            return $leaveBalance;
        } else {
            $this->_em->merge($leaveBalance);
            $this->_em->flush();
            return $leaveBalance;
        }
    }

    public function delete(LeaveBalance $leaveBalance): void
    {
        $this->_em->remove($leaveBalance);
        $this->_em->flush();
    }
}
