<?php

namespace App\Services;

use App\Http\Requests\StoreTicketRequest;
use App\Jobs\ProcessTicketAttachmentJob;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketService
{
    public function queryForUser(User $user): Builder
    {
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->userProfile;

        $companyId = $profile->company_id;
        $role = $profile->role;

        $base = $this->baseQuery($companyId);

        return match ($role) {
            'admin', 'manager' => $base,
            'employee' => $base->where(fn ($q) => $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id))
        };
    }

    public function getTickets(User $user, array $relations = [], ?int $perPage = null, ?array $filters = null, ?string $orderBy = 'created_at', ?string $orderDir = 'desc'): Collection|LengthAwarePaginator
    {
        $query = $this->queryForUser($user);

        if (! empty($relations)) {
            $query->with($relations);
        }

        if (! empty($filters)) {
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }
        }

        if ($orderBy && $orderDir) {
            $query->orderBy($orderBy, $orderDir);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function getTicketStatusCounts(User $user): array
    {
        return $this->queryForUser($user)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function queryForProject(Project $project, array $relations = []): Builder
    {
        $query = $this->baseQuery($project->company_id)
            ->where('project_id', $project->id);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    public function getTicketsForProject(Project $project, array $relations = [], ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->queryForProject($project, $relations);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function closeTicket(int $ticketId): Ticket
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->status = 'closed';
        $ticket->closed_at = now();
        $ticket->save();

        return $ticket;
    }

    public function createTicketFromRequest(StoreTicketRequest $request, User $user): Ticket
    {
        return DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();

            $ticket = Ticket::create([
                'project_id' => $validated['project_id'],
                'created_by' => $user->id,
                'assigned_to' => $validated['assigned_to'],
                'title' => $validated['title'],
                'status' => 'open',
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'] ?? null,
            ]);

            $file = $request->file('attachment');
            if ($file) {
                try {
                    $path = $file->storeAs('ticket_attachments', Str::uuid().'.'.$file->getClientOriginalExtension());

                    $ticket->ticketFileProcess()->create([
                        'file_path' => $path,
                        'status' => 'pending',
                    ]);

                    /** @var \App\Models\TicketFileProcess $ticketFileProcess */
                    $ticketFileProcess = $ticket->ticketFileProcess;
                    ProcessTicketAttachmentJob::dispatch($ticketFileProcess->id);
                } catch (\Exception $e) {
                    Log::error('File processing failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);
                    throw $e;
                }
            } else {
                /** @var \App\Models\User $recipient */
                $recipient = $ticket->recipient;
                $recipient->notify(new \App\Notifications\NewTicketNotification($ticket));
            }

            return $ticket;
        });
    }

    private function baseQuery(int $companyId): Builder
    {
        return Ticket::query()->withCompany($companyId);
    }
}
