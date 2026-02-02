<?php

use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('user profile belongs to a user', function () {
    $user = User::factory()
        ->has(
            UserProfile::factory(),
            'userProfile'
        )
        ->create();
    expect($user->userProfile)->toBeInstanceOf(UserProfile::class);
});

it('checks user role through isRole method', function () {
    $user = User::factory()
        ->has(
            UserProfile::factory()->state(['role' => 'manager']),
            'userProfile'
        )
        ->create();

    expect($user->isManager())->toBeTrue()
        ->and($user->isAdmin())->toBeFalse()
        ->and($user->isEmployee())->toBeFalse();
});

it('gets users created tickets', function () {
    $user = User::factory()->create();

    $createdTickets = Ticket::factory()
        ->count(3)
        ->create(['created_by' => $user->id]);

    Ticket::factory()
        ->count(2)
        ->create();

    $tickets = $user->createdTickets;

    expect($tickets)->toHaveCount(3)
        ->and($tickets->pluck('id')->sort()->values()->all())
        ->toBe($createdTickets->pluck('id')->sort()->values()->all());
});

it('gets users assigned tickets', function () {
    $userProfile = UserProfile::factory()
        ->state(['role' => 'employee'])
        ->create();

    $user = $userProfile->user;

    $assignedTickets = Ticket::factory()
        ->count(4)
        ->create(['assigned_to' => $user->id]);

    Ticket::factory()
        ->count(2)
        ->create();

    $tickets = $user->assignedTickets;

    expect($tickets)->toHaveCount(4)
        ->and($tickets->pluck('id')->sort()->values()->all())
        ->toBe($assignedTickets->pluck('id')->sort()->values()->all());
});

it('gets company through user profile', function () {
    $userProfile = UserProfile::factory()->create();
    $user = $userProfile->user;

    expect($user->company->id)->toBe($userProfile->company->id);
});
