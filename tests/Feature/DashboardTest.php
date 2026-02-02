<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserProfile;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $company = Company::factory()->create();

    $user = User::factory()->has(
        UserProfile::factory()->state([
            'company_id' => $company->id,
            'role' => 'employee',
        ])
    )->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
