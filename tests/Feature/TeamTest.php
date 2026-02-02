<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserProfile;

describe('GET /team', function () {
    test('authenticated user can view team page', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'manager'])
        )->create();

        $response = $this->actingAs($user)->get('/team');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('team/Index'));
    });

    test('employee can view team page', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $response = $this->actingAs($user)->get('/team');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('team/Index'));
    });

    test('admin can view team page', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'admin'])
        )->create();

        $response = $this->actingAs($user)->get('/team');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('team/Index'));
    });

    test('guest is redirected to login', function () {
        $response = $this->get('/team');

        $response->assertRedirect('/login');
    });
});
