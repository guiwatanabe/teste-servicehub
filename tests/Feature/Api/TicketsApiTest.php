<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;

describe('GET /api/tickets', function () {
    test('user can list their tickets', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $tickets = Ticket::factory(3)->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/tickets');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('employee only sees their created or assigned tickets', function () {
        $company = Company::factory()->create();
        $employee1 = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $employee2 = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        Ticket::factory(2)->create(['project_id' => $project->id, 'created_by' => $employee1->id]);
        Ticket::factory(2)->create(['project_id' => $project->id, 'created_by' => $employee2->id]);

        $response = $this->actingAs($employee1)->getJson('/api/tickets');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('manager sees all company tickets', function () {
        $company = Company::factory()->create();
        $manager = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        Ticket::factory(5)->create(['project_id' => $project->id, 'created_by' => $employee->id]);

        $response = $this->actingAs($manager)->getJson('/api/tickets');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('filter by status works', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        Ticket::factory(3)->create(['project_id' => $project->id, 'status' => 'open']);
        Ticket::factory(2)->create(['project_id' => $project->id, 'status' => 'closed']);

        $response = $this->actingAs($user)->getJson('/api/tickets?status=open');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('filter by priority works', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        Ticket::factory(3)->create(['project_id' => $project->id, 'priority' => 'high']);
        Ticket::factory(2)->create(['project_id' => $project->id, 'priority' => 'low']);

        $response = $this->actingAs($user)->getJson('/api/tickets?priority=high');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('invalid filter value returns validation error', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $response = $this->actingAs($user)->getJson('/api/tickets?status=invalid');

        $response->assertUnprocessable();
    });

    test('unauthenticated user cannot list tickets', function () {
        $response = $this->getJson('/api/tickets');

        $response->assertUnauthorized();
    });
});

describe('GET /api/tickets/{ticket}', function () {
    test('authorized user can view ticket', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.title', $ticket->title);
    });

    test('employee can view their own ticket', function () {
        $company = Company::factory()->create();
        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'created_by' => $employee->id,
        ]);

        $response = $this->actingAs($employee)->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
    });

    test('employee cannot view other employee ticket', function () {
        $company = Company::factory()->create();
        $employee1 = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $employee2 = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'created_by' => $employee2->id,
        ]);

        $response = $this->actingAs($employee1)->getJson("/api/tickets/{$ticket->id}");

        $response->assertForbidden();
    });

    test('unauthenticated user cannot view ticket', function () {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertUnauthorized();
    });
});

describe('POST /api/tickets/{ticket}/close', function () {
    test('authorized user can close ticket', function () {
        $company = Company::factory()->create();
        $manager = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($manager)->postJson("/api/tickets/{$ticket->id}/close", ['id' => $ticket->id]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
        ]);
    });

    test('employee can close their own ticket', function () {
        $company = Company::factory()->create();
        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'created_by' => $employee->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($employee)->postJson("/api/tickets/{$ticket->id}/close", ['id' => $ticket->id]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'closed');
    });

    test('unauthorized user cannot close ticket', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company1->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company2->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($employee)->postJson("/api/tickets/{$ticket->id}/close");

        $response->assertForbidden();
    });

    test('unauthenticated user cannot close ticket', function () {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/close");

        $response->assertUnauthorized();
    });
});
