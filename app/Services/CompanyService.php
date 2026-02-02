<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class CompanyService
{
    public function getVisibleCompanies(User $user)
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $companyId = $profile->company_id;

        return Company::where('id', $companyId)->get();
    }
}
