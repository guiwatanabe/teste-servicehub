<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;

test('manager can authorize ticket creation', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $this->actingAs($manager);
    $this->assertTrue($manager->can('create', Ticket::class));
});

test('employee cannot authorize ticket creation', function () {
    $company = Company::factory()->create();

    $employee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $this->actingAs($employee);
    $this->assertFalse($employee->can('create', Ticket::class));
});

test('validation fails when title is missing', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->create(['company_id' => $company->id]);

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $assignee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $this->actingAs($manager);
    $response = $this->post('/tickets', [
        'project_id' => $project->id,
        'priority' => 'high',
        'assigned_to' => $assignee->id,
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('title');
});

test('validation fails when project_id does not belong to user company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company1->id,
            'role' => 'manager',
        ])
    )->create();

    $assignee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company1->id,
            'role' => 'employee',
        ])
    )->create();

    // Project belongs to company2
    $project = Project::factory()->create(['company_id' => $company2->id]);

    $this->actingAs($manager);
    $response = $this->post('/tickets', [
        'title' => 'Test Ticket',
        'project_id' => $project->id,
        'priority' => 'high',
        'assigned_to' => $assignee->id,
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('project_id');
});

test('validation fails when assigned_to user is from different company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company1->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company1->id]);

    // Assignee from different company
    $assignee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company2->id,
            'role' => 'employee',
        ])
    )->create();

    $this->actingAs($manager);
    $response = $this->post('/tickets', [
        'title' => 'Test Ticket',
        'project_id' => $project->id,
        'priority' => 'high',
        'assigned_to' => $assignee->id,
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('assigned_to');
});

test('validation passes with valid ticket data', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);

    $assignee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $this->actingAs($manager);
    $response = $this->post('/tickets', [
        'title' => 'Test Ticket',
        'project_id' => $project->id,
        'priority' => 'high',
        'assigned_to' => $assignee->id,
        'due_date' => '2026-02-15',
    ]);

    $response->assertRedirect();
});

test('validation fails with invalid priority', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);

    $assignee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $this->actingAs($manager);
    $response = $this->post('/tickets', [
        'title' => 'Test Ticket',
        'project_id' => $project->id,
        'priority' => 'invalid',
        'assigned_to' => $assignee->id,
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('priority');
});

test('validation fails with past due_date', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);

    $assignee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $this->actingAs($manager);
    $response = $this->post('/tickets', [
        'title' => 'Test Ticket',
        'project_id' => $project->id,
        'priority' => 'high',
        'assigned_to' => $assignee->id,
        'due_date' => '2025-01-01',
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('due_date');
});
