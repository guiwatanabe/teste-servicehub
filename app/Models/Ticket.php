<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Illuminate\Database\Eloquent\Builder withProject(int $projectId)
 */
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'status',
        'priority',
        'due_date',
        'closed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime:Y-m-d',
        'closed_at' => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function ticketDetail(): HasOne
    {
        return $this->hasOne(TicketDetail::class);
    }

    public function ticketFileProcess(): HasOne
    {
        return $this->hasOne(TicketFileProcess::class);
    }

    public function company(): BelongsTo
    {
        /** @var \App\Models\Project $project */
        $project = $this->project;

        return $project->company();
    }

    #[Scope]
    protected function withCompany(Builder $query, int $companyId): void
    {
        $query->whereHas('project', fn ($q) => $q->where('company_id', $companyId));
    }

    #[Scope]
    protected function withRecipient(Builder $query, int $userId): void
    {
        $query->where('assigned_to', $userId);
    }

    #[Scope]
    protected function withCreator(Builder $query, int $userId): void
    {
        $query->where('created_by', $userId);
    }

    #[Scope]
    protected function withProject(Builder $query, int $projectId): void
    {
        $query->where('project_id', $projectId);
    }
}
