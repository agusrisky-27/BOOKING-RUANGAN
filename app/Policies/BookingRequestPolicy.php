<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\BookingRequest;
use App\Models\User;

class BookingRequestPolicy
{
    /**
     * Determine whether the user can view the booking request.
     */
    public function view(User $user, BookingRequest $bookingRequest): bool
    {
        if ($user->role === UserRole::MAHASISWA) {
            return $bookingRequest->user_id === $user->id;
        }

        if ($user->role === UserRole::WR2) {
            // WR2 can view anything except unfinished drafts
            return $bookingRequest->status !== BookingStatus::DRAFT;
        }

        if ($user->role === UserRole::SARPRAS) {
            // Sarpras only sees requests that passed WR2 or are processed
            return ! in_array($bookingRequest->status, [
                BookingStatus::DRAFT,
                BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                BookingStatus::DITOLAK_WR2,
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can edit the draft.
     */
    public function update(User $user, BookingRequest $bookingRequest): bool
    {
        return $bookingRequest->isEditableBy($user);
    }

    /**
     * Determine whether the user can submit the draft.
     */
    public function submit(User $user, BookingRequest $bookingRequest): bool
    {
        return $user->role === UserRole::MAHASISWA
            && $bookingRequest->user_id === $user->id
            && $bookingRequest->status === BookingStatus::DRAFT;
    }

    /**
     * Determine whether the user can cancel the request.
     */
    public function cancel(User $user, BookingRequest $bookingRequest): bool
    {
        return $bookingRequest->isCancelableBy($user);
    }

    /**
     * Determine whether WR2 can approve/reject.
     */
    public function processWr2(User $user, BookingRequest $bookingRequest): bool
    {
        return $user->role === UserRole::WR2
            && $bookingRequest->status === BookingStatus::MENUNGGU_PERSETUJUAN_WR2;
    }

    /**
     * Determine whether Sarpras can process/confirm/reject.
     */
    public function processSarpras(User $user, BookingRequest $bookingRequest): bool
    {
        return $user->role === UserRole::SARPRAS
            && in_array($bookingRequest->status, [
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DIPROSES_SARPRAS,
            ]);
    }

    /**
     * Determine whether user can download attachments.
     */
    public function downloadAttachment(User $user, BookingRequest $bookingRequest): bool
    {
        return $this->view($user, $bookingRequest);
    }
}
