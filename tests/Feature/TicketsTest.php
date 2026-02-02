<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;

describe('GET /tickets', function () {
    test('authenticated user can list tickets', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        Ticket::factory(5)->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tickets/Index')
                ->has('tickets.data', 5)
                ->has('canCreateTickets')
            );
    });

    test('guest is redirected to login', function () {
        $response = $this->get('/tickets');

        $response->assertRedirect('/login');
    });

    test('pagination works', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        Ticket::factory(15)->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->get('/tickets?page=2');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('tickets.meta.current_page')
                ->where('tickets.meta.current_page', 2)
            );
    });

    test('employee sees only their tickets', function () {
        $company = Company::factory()->create();
        $employee1 = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $employee2 = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        Ticket::factory(3)->create(['project_id' => $project->id, 'created_by' => $employee1->id]);
        Ticket::factory(2)->create(['project_id' => $project->id, 'created_by' => $employee2->id]);

        $response = $this->actingAs($employee1)->get('/tickets');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->has('tickets.data', 3));
    });
});

describe('GET /tickets/create', function () {
    test('manager can view create ticket form', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get('/tickets/create');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tickets/Create')
                ->has('projects')
                ->has('teamMembers')
            );
    });

    test('admin can view create ticket form', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'admin'])
        )->create();

        $response = $this->actingAs($user)->get('/tickets/create');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('tickets/Create'));
    });

    test('employee cannot view create ticket form', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $response = $this->actingAs($user)->get('/tickets/create');

        $response->assertForbidden();
    });

    test('guest is redirected to login', function () {
        $response = $this->get('/tickets/create');

        $response->assertRedirect('/login');
    });
});

describe('POST /tickets', function () {
    test('manager can create ticket', function () {
        $company = Company::factory()->create();
        $manager = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($manager)->post('/tickets', [
            'title' => 'Test Ticket',
            'project_id' => $project->id,
            'priority' => 'high',
            'assigned_to' => $employee->id,
            'due_date' => '2026-02-15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', ['title' => 'Test Ticket']);
    });

    test('admin can create ticket', function () {
        $company = Company::factory()->create();
        $admin = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'admin'])
        )->create();

        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($admin)->post('/tickets', [
            'title' => 'Admin Ticket',
            'project_id' => $project->id,
            'priority' => 'medium',
            'assigned_to' => $employee->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', ['title' => 'Admin Ticket']);
    });

    test('employee cannot create ticket', function () {
        $company = Company::factory()->create();
        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($employee)->post('/tickets', [
            'title' => 'Test Ticket',
            'project_id' => $project->id,
            'priority' => 'high',
            'assigned_to' => $employee->id,
        ]);

        $response->assertForbidden();
    });

    test('guest is redirected to login', function () {
        $response = $this->post('/tickets', []);

        $response->assertRedirect('/login');
    });

    test('validation fails with invalid data', function () {
        $company = Company::factory()->create();
        $manager = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $response = $this->actingAs($manager)->post('/tickets', [
            'title' => '',
            'project_id' => 999,
            'priority' => 'invalid',
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['title', 'project_id', 'priority', 'assigned_to']);
    });
});

describe('GET /tickets/{ticket}', function () {
    test('authorized user can view ticket', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->get("/tickets/{$ticket->id}");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tickets/Show')
                ->has('ticket')
            );
    });

    test('employee can view their own created ticket', function () {
        $company = Company::factory()->create();
        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'created_by' => $employee->id,
        ]);

        $response = $this->actingAs($employee)->get("/tickets/{$ticket->id}");

        $response->assertOk();
    });

    test('employee can view their assigned ticket', function () {
        $company = Company::factory()->create();
        $employee = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $employee->id,
        ]);

        $response = $this->actingAs($employee)->get("/tickets/{$ticket->id}");

        $response->assertOk();
    });

    test('unauthorized employee gets forbidden', function () {
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

        $response = $this->actingAs($employee1)->get("/tickets/{$ticket->id}");

        $response->assertForbidden();
    });

    test('user from different company gets forbidden', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company1->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company2->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->get("/tickets/{$ticket->id}");

        $response->assertForbidden();
    });

    test('guest is redirected to login', function () {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $ticket = Ticket::factory()->create(['project_id' => $project->id]);

        $response = $this->get("/tickets/{$ticket->id}");

        $response->assertRedirect('/login');
    });

    test('non-existent ticket returns 404', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $response = $this->actingAs($user)->get('/tickets/99999');

        $response->assertNotFound();
    });
});
