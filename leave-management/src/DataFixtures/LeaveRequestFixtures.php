<?php

namespace App\DataFixtures;

use App\DTO\LeaveRequestDTO;
use App\Entity\User;
use App\Service\LeaveRequest\LeaveRequestPersistenceService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Psr\Log\LoggerInterface;
use ReflectionException;

class LeaveRequestFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly LeaveRequestPersistenceService $leaveRequestService,
        private readonly LoggerInterface $logger
    )
    {}


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

        $teamLead = $this->getReference(UserFixtures::TEAM_LEAD_REFERENCE);

        $this->logger->info("Team lead id: {$teamLead->getId()}");

        for ($i = 0; $i < 5; $i++) {
            $leaveRequestDTO = new LeaveRequestDTO(
                userId: $teamLead->getId(),
                startDate: $faker->dateTimeBetween('-10 days'),
                endDate: $faker->dateTimeBetween('now', '+10 days'),
                reason: $faker->text
            );

            $this->leaveRequestService->createLeaveRequest($leaveRequestDTO);
        }
    }
}
