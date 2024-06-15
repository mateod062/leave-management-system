<?php

namespace App\Service\LeaveRequest\Interface;

use App\DTO\LeaveRequestDTO;

interface LeaveRequestPersistenceServiceInterface
{
    /**
     * Create a new leave request
     *
     * @param LeaveRequestDTO $leaveRequest
     * @return LeaveRequestDTO
     */
    public function createLeaveRequest(LeaveRequestDTO $leaveRequest): LeaveRequestDTO;

    /**
     * Update a leave request
     *
     * @param LeaveRequestDTO $leaveRequestDTO
     * @return LeaveRequestDTO
     */
    public function updateLeaveRequest(LeaveRequestDTO $leaveRequestDTO): LeaveRequestDTO;


    /**
     * Approve a leave request
     *
     * @param int $leaveRequestId
     * @return void
     */
    public function approveLeaveRequest(int $leaveRequestId): void;

    /**
     * Reject a leave request
     *
     * @param int $leaveRequestId
     * @return void
     */
    public function rejectLeaveRequest(int $leaveRequestId): void;

    /**
     * Delete a leave request
     *
     * @param int $leaveRequestId
     * @return void
     */
    public function deleteLeaveRequest(int $leaveRequestId): void;
}