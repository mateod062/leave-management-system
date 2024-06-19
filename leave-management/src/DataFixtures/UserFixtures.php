<?php

namespace App\DataFixtures;

use App\DTO\UserCreationDTO;
use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\UserRepository;
use App\Service\User\UserPersistenceService;
use App\Service\User\UserQueryService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use ReflectionException;

class UserFixtures extends Fixture
{
    public const TEAM_LEAD_REFERENCE = 'team-lead';
    public const PROJECT_MANAGER_REFERENCE = 'project-manager';
    public const ADMIN_REFERENCE = 'admin';

    public function __construct(
        private readonly UserPersistenceService $userPersistenceService,
        private readonly UserRepository $userRepository,
        private array $usernames = [],
        private array $emails = []
    ) {}

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        
        // Helper function to generate unique values
        $uniqueValue = function (callable $generator, array &$existingValues) {
            do {
                $value = $generator();
            } while (in_array($value, $existingValues));
            $existingValues[] = $value;
            return $value;
        };

        $admin = new UserCreationDTO(
            username: 'admin',
            email: 'admin@gmail.com',
            password: 'admin',
        );
        $this->userPersistenceService->createAdmin($admin);
        $createdAdmin = $this->userRepository->findOneBy(['email' => $admin->getEmail()]);
        $this->addReference(self::ADMIN_REFERENCE, $createdAdmin);

        $projectManager = new UserCreationDTO(
            username: $uniqueValue([$faker, 'userName'], $this->usernames),
            email: $uniqueValue([$faker, 'email'], $this->emails),
            password: $faker->password,

        );

        $this->userPersistenceService->createProjectManager($projectManager);
        $createdProjectManager = $this->userRepository->findOneBy(['email' => $projectManager->getEmail()]);
        $this->addReference(self::PROJECT_MANAGER_REFERENCE, $createdProjectManager);

        $teamLead = new UserCreationDTO(
            username: $uniqueValue([$faker, 'userName'], $this->usernames),
            email: $uniqueValue([$faker, 'email'], $this->emails),
            password: $faker->password,
        );

        $this->userPersistenceService->createTeamLead($teamLead);
        $createdTeamLead = $this->userRepository->findOneBy(['email' => $teamLead->getEmail()]);
        $this->addReference(self::TEAM_LEAD_REFERENCE, $createdTeamLead);

        for ($i = 0; $i < 10; $i++) {
            $employee = new UserCreationDTO(
                username: $uniqueValue([$faker, 'userName'], $this->usernames),
                email: $uniqueValue([$faker, 'email'], $this->emails),
                password: $faker->password,
            );
            $this->userPersistenceService->createEmployee($employee);
        }
    }
}