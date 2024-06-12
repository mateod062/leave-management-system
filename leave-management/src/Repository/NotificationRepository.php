<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $notification): Notification
    {
        if (!$this->getEntityManager()->contains($notification)) {
            $this->getEntityManager()->persist($notification);
        }

        $this->getEntityManager()->flush();

        return $notification;
    }

    public function delete(Notification $notification): void
    {
        $this->getEntityManager()->remove($notification);
        $this->getEntityManager()->flush();
    }
}
