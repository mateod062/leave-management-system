<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function save(Team $team): Team
    {
        if ($team->getId() === null) {
            $this->_em->persist($team);
            $this->_em->flush();
            return $team;
        } else {
            $this->_em->merge($team);
            $this->_em->flush();
            return $team;
        }
    }

    public function delete(Team $team): void
    {
        $this->_em->remove($team);
        $this->_em->flush();
    }
}
