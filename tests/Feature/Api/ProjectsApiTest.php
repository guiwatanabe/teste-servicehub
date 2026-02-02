<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;

describe('GET /api/projects', function () {
    test('user can list their company projects', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $projects = Project::factory(3)->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('employee cannot see other company projects', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company1->id, 'role' => 'employee'])
        )->create();

        Project::factory(3)->create(['company_id' => $company2->id]);

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('unauthenticated user cannot list projects', function () {
        $response = $this->getJson('/api/projects');

        $response->assertUnauthorized();
    });
});

describe('GET /api/projects/{project}/tickets', function () {
    test('user can list tickets for their company project', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);
        $tickets = Ticket::factory(3)->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}/tickets");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('user cannot access project from different company', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company1->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}/tickets");

        $response->assertForbidden();
    });

    test('unauthenticated user cannot list project tickets', function () {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->getJson("/api/projects/{$project->id}/tickets");

        $response->assertUnauthorized();
    });
});
