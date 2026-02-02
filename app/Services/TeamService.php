<?php

namespace App\Services;

use App\Models\User;

class TeamService
{
    public function getTeamMembers(User $user, ?array $role = [])
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $companyId = $profile->company_id;

        return $this->getCompanyTeamMembers($companyId, $role);
    }

    private function getCompanyTeamMembers($companyId, ?array $role = [])
    {
        return User::with(['userProfile'])
            ->whereHas('userProfile', function ($query) use ($companyId, $role) {
                $query->where('company_id', $companyId);
                if ($role !== null && ! empty($role)) {
                    $query->whereIn('role', $role);
                }
            })
            ->get();
    }
}
