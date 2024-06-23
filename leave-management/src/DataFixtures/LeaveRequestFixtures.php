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
use ReflectionException;

class LeaveRequestFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly LeaveRequestPersistenceService $leaveRequestService,
    )
    {}


    public function getDependencies(): array
    {
        return [
            TeamFixtures::class
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

        $teamMembers = $manager->getRepository(User::class)->findAll();

        foreach ($teamMembers as $teamMember) {
            for ($i = 0; $i < 5; $i++) {
                $leaveRequestDTO = new LeaveRequestDTO(
                    userId: $teamMember->getId(),
                    startDate: $faker->dateTimeBetween('-10 days'),
                    endDate: $faker->dateTimeBetween('now', '+10 days'),
                    reason: $faker->text
                );

                $this->leaveRequestService->createLeaveRequest($leaveRequestDTO);
            }
        }
    }
}
