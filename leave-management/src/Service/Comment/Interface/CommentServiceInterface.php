<?php

namespace App\Service\Comment\Interface;

use App\DTO\CommentDTO;

interface CommentServiceInterface
{
    /**
     * Get all comments for a leave request
     *
     * @param int $leaveRequestId
     * @return array
     */
    public function getComments(int $leaveRequestId): array;

    /**
     * Get all replies for a comment
     *
     * @param int $commentId
     * @return array
     */
    public function getReplies(int $commentId): array;

    /**
     * Add a new comment
     *
     * @param CommentDTO $comment
     * @return CommentDTO
     */
    public function addComment(CommentDTO $comment): CommentDTO;

    /**
     * Update a comment
     *
     * @param CommentDTO $comment
     * @return CommentDTO
     */
    public function updateComment(CommentDTO $comment): CommentDTO;

    /**
     * Delete a comment
     *
     * @param int $commentId
     * @return void
     */
    public function deleteComment(int $commentId): void;
}