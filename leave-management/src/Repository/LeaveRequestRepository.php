<?php

namespace App\Repository;

use App\Entity\LeaveRequest;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeaveRequest>
 *
 * @method LeaveRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaveRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaveRequest[]    findAll()
 * @method LeaveRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaveRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaveRequest::class);
    }

    public function save(LeaveRequest $leaveRequest): LeaveRequest
    {
        if (!$this->getEntityManager()->contains($leaveRequest)) {
            $this->getEntityManager()->persist($leaveRequest);
        }

        $this->getEntityManager()->flush();

        return $leaveRequest;
    }

    public function delete(LeaveRequest $leaveRequest): void
    {
        $this->getEntityManager()->remove($leaveRequest);
        $this->getEntityManager()->flush();
    }

    public function findByTeamAndMonth(int $teamId, int $month, int $year): array
    {
        $startDate = new DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('last day of this month');

        return $this->createQueryBuilder('lr')
            ->innerJoin('lr.user', 'u')
            ->andWhere('u.team = :teamId')
            ->andWhere('lr.startDate BETWEEN :startDate AND :endDate')
            ->setParameter('teamId', $teamId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    public function findOverlapping(User $user, DateTime $startDate, DateTime $endDate): array
    {
        return $this->createQueryBuilder('lr')
            ->where('lr.user = :user')
            ->andWhere('lr.status = \'approved\'')
            ->andWhere('(
            (lr.startDate <= :endDate AND lr.endDate >= :startDate)
            )')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
