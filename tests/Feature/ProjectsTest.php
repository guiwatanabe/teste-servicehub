<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;

describe('GET /projects', function () {
    test('user can list their company projects', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        Project::factory(5)->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get('/projects');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('projects/Index')
                ->has('projects.data', 5)
            );
    });

    test('employee can list company projects', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        Project::factory(3)->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get('/projects');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->has('projects.data', 3));
    });

    test('user cannot see other company projects', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company1->id, 'role' => 'manager'])
        )->create();

        Project::factory(3)->create(['company_id' => $company2->id]);

        $response = $this->actingAs($user)->get('/projects');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->has('projects.data', 0));
    });

    test('pagination works', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        Project::factory(15)->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get('/projects?page=2');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('projects.meta.current_page')
                ->where('projects.meta.current_page', 2)
            );
    });

    test('guest is redirected to login', function () {
        $response = $this->get('/projects');

        $response->assertRedirect('/login');
    });
});

describe('GET /projects/{project}', function () {
    test('authorized user can view project', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get("/projects/{$project->id}");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('projects/Show')
                ->has('project')
            );
    });

    test('employee can view project', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get("/projects/{$project->id}");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('projects/Show'));
    });

    test('user cannot view other company project', function () {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company1->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($user)->get("/projects/{$project->id}");

        $response->assertForbidden();
    });

    test('guest is redirected to login', function () {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->get("/projects/{$project->id}");

        $response->assertRedirect('/login');
    });

    test('non-existent project returns 404', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $response = $this->actingAs($user)->get('/projects/99999');

        $response->assertNotFound();
    });

    test('project page renders successfully with data', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get("/projects/{$project->id}");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('projects/Show')
                ->has('project')
            );
    });
});
