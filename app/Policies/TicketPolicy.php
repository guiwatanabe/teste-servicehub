<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $companyId = $profile->company_id;
        $role = $profile->role;

        if ($role === 'employee') {
            return $ticket->assigned_to === $user->id || $ticket->created_by === $user->id;
        }

        /** @var \App\Models\Project $project */
        $project = $ticket->project;

        return $project->company_id === $profile->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $role = $profile->role;

        return in_array($role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $role = $profile->role;

        return in_array($role, ['admin', 'manager']) || $ticket->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $role = $profile->role;

        return $role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $role = $profile->role;

        return $role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $role = $profile->role;

        return $role === 'admin';
    }

    /**
     * Determine whether the user can close the ticket.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $role = $profile->role;

        return in_array($role, ['admin', 'manager']) || $ticket->created_by === $user->id || $ticket->assigned_to === $user->id;
    }
}
