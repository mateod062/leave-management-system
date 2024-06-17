<?php

namespace App\DataFixtures;

use App\DTO\CommentDTO;
use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Service\Comment\CommentService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use ReflectionException;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private function __construct(
        private readonly CommentService $commentService,
    )
    {}

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            LeaveRequestFixtures::class
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $leaveRequests = $manager->getRepository(LeaveRequest::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();
        $comments = [];

        foreach ($leaveRequests as $leaveRequest) {
            for ($i = 0; $i < 5; $i++) {
                $commentDTO = new CommentDTO(
                    userId: $faker->randomElement($users)->getId(),
                    leaveRequestId: $leaveRequest->getId(),
                    comment: $faker->text
                );

                $this->commentService->addComment($commentDTO);
                $comments[] = $commentDTO;
            }
        }

        foreach ($comments as $comment) {
            $replyDTO = new CommentDTO(
                userId: $faker->randomElement($users)->getId(),
                leaveRequestId: $comment->leaveRequestId,
                parentCommentId: $faker->randomElement([null, $faker->randomElement($comments)->id]),
                comment: $faker->text
            );

            $this->commentService->addComment($replyDTO);
        }
    }
}