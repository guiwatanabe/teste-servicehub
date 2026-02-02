<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a company and has many tickets', function () {
    $project = Project::factory()->create();

    $creator = User::factory()->create();
    $recipient = User::factory()->create();

    Ticket::factory()->create([
        'project_id' => $project->id,
        'created_by' => $creator->id,
        'assigned_to' => $recipient->id,
    ]);

    Ticket::factory()->create([
        'project_id' => $project->id,
        'created_by' => $creator->id,
        'assigned_to' => $recipient->id,
    ]);

    expect($project->company)->toBeInstanceOf(Company::class)
        ->and($project->tickets)->toHaveCount(2);
});

it('filters projects by company scope', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    Project::factory()->count(2)->create(['company_id' => $companyA->id]);
    Project::factory()->create(['company_id' => $companyB->id]);

    $projects = Project::query()->withCompany($companyA->id)->get();

    expect($projects)->toHaveCount(2)
        ->and($projects->pluck('company_id')->unique()->values()->all())
        ->toBe([$companyA->id]);
});
