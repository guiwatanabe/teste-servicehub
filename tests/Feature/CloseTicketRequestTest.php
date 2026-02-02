<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;

test('manager can authorize closing a ticket', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $this->actingAs($manager);
    $this->assertTrue($manager->can('close', $ticket));
});

test('employee can close their own created ticket', function () {
    $company = Company::factory()->create();

    $employee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'created_by' => $employee->id,
    ]);

    $this->actingAs($employee);
    $this->assertTrue($employee->can('close', $ticket));
});

test('employee can close their assigned ticket', function () {
    $company = Company::factory()->create();

    $employee = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $employee->id,
    ]);

    $this->actingAs($employee);
    $this->assertTrue($employee->can('close', $ticket));
});

test('employee cannot close ticket not created or assigned to them', function () {
    $company = Company::factory()->create();

    $employee1 = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $employee2 = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'created_by' => $employee2->id,
        'assigned_to' => $employee2->id,
    ]);

    $this->actingAs($employee1);
    $this->assertFalse($employee1->can('close', $ticket));
});

test('admin can close any ticket', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $admin = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company1->id,
            'role' => 'admin',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company2->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $this->actingAs($admin);
    $this->assertTrue($admin->can('close', $ticket));
});

test('closing ticket via API endpoint updates status', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'status' => 'open',
    ]);

    $this->actingAs($manager);
    $response = $this->postJson("/api/tickets/{$ticket->id}/close", ['id' => $ticket->id]);

    $response->assertOk();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
    ]);
    $this->assertNotNull($ticket->refresh()->closed_at);
});

test('unauthorized user cannot close ticket', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $employee1 = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company1->id,
            'role' => 'employee',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company2->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $this->actingAs($employee1);
    $response = $this->postJson("/api/tickets/{$ticket->id}/close");

    $response->assertForbidden();
});

test('guest cannot close ticket', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create(['project_id' => $project->id]);

    $response = $this->postJson("/api/tickets/{$ticket->id}/close");

    $response->assertUnauthorized();
});

test('closing non-existent ticket returns 404', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $this->actingAs($manager);
    $response = $this->postJson('/api/tickets/99999/close');

    $response->assertNotFound();
});

test('closing already closed ticket succeeds', function () {
    $company = Company::factory()->create();

    $manager = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'manager',
        ])
    )->create();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'status' => 'closed',
        'closed_at' => now(),
    ]);

    $this->actingAs($manager);
    $response = $this->postJson("/api/tickets/{$ticket->id}/close", ['id' => $ticket->id]);

    $response->assertOk();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
    ]);
});
