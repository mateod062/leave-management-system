<?php

namespace App\DataFixtures;

use App\DTO\TeamCreationDTO;
use App\Service\Team\TeamService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use ReflectionException;

class TeamFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly TeamService $teamService,
    ) {}

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $teamDTO = new TeamCreationDTO(
            name: $faker->company,
            membersIds: [
                $this->getReference(UserFixtures::TEAM_LEAD_REFERENCE)->getId(),
                $this->getReference(UserFixtures::PROJECT_MANAGER_REFERENCE)->getId()
            ],
            teamLeadId: $this->getReference(UserFixtures::TEAM_LEAD_REFERENCE)->getId(),
            projectManagerId: $this->getReference(UserFixtures::PROJECT_MANAGER_REFERENCE)->getId()
        );

        $this->teamService->createTeam($teamDTO);
    }
}