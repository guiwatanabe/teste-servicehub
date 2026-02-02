<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has many projects', function () {
    $company = Company::factory()->create();
    $project1 = Project::factory()->create(['company_id' => $company->id]);
    $project2 = Project::factory()->create(['company_id' => $company->id]);

    expect($company->projects)->toHaveCount(2)
        ->and($company->projects->pluck('id')->all())
        ->toBe([$project1->id, $project2->id]);
});

it('has many users and user profiles', function () {
    $company = Company::factory()->create();

    User::factory()
        ->count(2)
        ->has(
            UserProfile::factory()->state([
                'company_id' => $company->id,
                'role' => 'employee',
            ])
        )
        ->create();

    expect($company->users)->toHaveCount(2)
        ->and($company->userProfiles)->toHaveCount(2);
});

it('has admins managers and employees', function () {
    $company = Company::factory()->create();

    User::factory()
        ->count(3)
        ->has(
            UserProfile::factory()
                ->state([
                    'company_id' => $company->id,
                ])
                ->sequence(
                    ['role' => 'admin'],
                    ['role' => 'manager'],
                    ['role' => 'employee'],
                )
        )
        ->create();

    expect($company->admins)->toHaveCount(1)
        ->and($company->managers)->toHaveCount(1)
        ->and($company->employees)->toHaveCount(1);
});

it('has many tickets through projects', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->create(['company_id' => $company->id]);

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

    expect($company->tickets)->toHaveCount(2);
});
