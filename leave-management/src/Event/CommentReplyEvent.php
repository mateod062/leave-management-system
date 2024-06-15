<?php

namespace App\Event;

use App\Entity\Comment;
use Symfony\Contracts\EventDispatcher\Event;

class CommentReplyEvent extends Event
{
    public const NAME = 'comment.reply';

    public function __construct(
        private readonly Comment $comment
    )
    {}

    public function getComment(): Comment
    {
        return $this->comment;
    }
}