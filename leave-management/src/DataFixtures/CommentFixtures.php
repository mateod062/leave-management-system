<?php

namespace App\DataFixtures;

use App\DTO\CommentCreationDTO;
use App\DTO\CommentResponseDTO;
use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Service\Comment\CommentService;
use App\Service\Comment\Interface\CommentServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use ReflectionException;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly CommentServiceInterface $commentService,
    )
    {}

    public function getDependencies(): array
    {
        return [
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
                $commentDTO = new CommentCreationDTO(
                    userId: $faker->randomElement($users)->getId(),
                    leaveRequestId: $leaveRequest->getId(),
                    message: $faker->text
                );

                $savedComment = $this->commentService->addComment($commentDTO);
                $comments[] = $savedComment;
            }
        }


        foreach ($comments as $comment) {
            $replyDTO = new CommentCreationDTO(
                userId: $faker->randomElement($users)->getId(),
                leaveRequestId: $comment->getLeaveRequestId(),
                parentCommentId: $faker->randomElement([null, $faker->randomElement($comments)->getId()]),
                message: $faker->text
            );

            $this->commentService->addComment($replyDTO);
        }
    }
}