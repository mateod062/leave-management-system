<?php

namespace App\Service\Comment\Interface;

use App\DTO\CommentCreationDTO;
use App\DTO\CommentResponseDTO;

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
     * @param CommentCreationDTO $comment
     * @return CommentResponseDTO
     */
    public function addComment(CommentCreationDTO $comment): CommentResponseDTO;

    /**
     * Update a comment
     *
     * @param int $id
     * @param CommentCreationDTO $comment
     * @return CommentResponseDTO
     */
    public function updateComment(int $id, CommentCreationDTO $comment): CommentResponseDTO;

    /**
     * Delete a comment
     *
     * @param int $commentId
     * @return void
     */
    public function deleteComment(int $commentId): void;
}