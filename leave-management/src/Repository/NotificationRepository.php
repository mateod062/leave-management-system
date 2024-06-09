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
        if ($notification->getId() === null) {
            $this->_em->persist($notification);
            $this->_em->flush();
            return $notification;
        } else {
            $this->_em->merge($notification);
            $this->_em->flush();
            return $notification;
        }
    }

    public function delete(Notification $notification): void
    {
        $this->_em->remove($notification);
        $this->_em->flush();
    }
}
