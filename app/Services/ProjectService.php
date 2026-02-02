<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectService
{
    public function __construct(private TicketService $ticketService) {}

    public function getProjectsForCompany(Company $company, array $relations = [], ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->baseQuery($company->id);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function getProjectsForUser(User $user, array $relations = [], ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->queryForUser($user);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function queryForUserWithTicketCounts(User $user): Builder
    {
        $scopedTicketIds = $this->scopedTicketIdsForUser($user);

        return $this->queryForUser($user)->withCount(
            $this->ticketCounts($scopedTicketIds),
        );
    }

    public function loadTicketCountsForUser(User $user, Project $project): Project
    {
        $scopedTicketIds = $this->scopedTicketIdsForUser($user);

        return $project->loadCount(
            $this->ticketCounts($scopedTicketIds),
        );
    }

    public function queryForUser(User $user): Builder
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;
        $companyId = $profile->company_id;

        return $this->baseQuery($companyId);
    }

    private function baseQuery(int $companyId): Builder
    {
        return Project::query()->where('company_id', $companyId);
    }

    private function scopedTicketIdsForUser(User $user): Builder
    {
        return $this->ticketService
            ->queryForUser($user)
            ->select('tickets.id');
    }

    private function ticketCounts(Builder $scopedTicketIds): array
    {
        $scope = fn ($q) => $q->whereIn('tickets.id', $scopedTicketIds);

        return [
            'tickets as tickets_total' => $scope,
            'tickets as tickets_open' => fn ($q) => $scope($q)->where('status', 'open'),
            'tickets as tickets_in_progress' => fn ($q) => $scope($q)->where('status', 'in_progress'),
            'tickets as tickets_closed' => fn ($q) => $scope($q)->where('status', 'closed'),
        ];
    }
}
