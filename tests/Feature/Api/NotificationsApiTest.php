<?php

use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\TicketDetailsAvailableNotification;

describe('POST /api/notifications/{id}/read', function () {
    test('user can mark their notification as read', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $user->notify(new TicketDetailsAvailableNotification(new Ticket(['title' => 'Test ticket'])));

        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->postJson("/api/notifications/{$notification->id}/read")
            ->assertRedirect();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    });

    test('unauthenticated user cannot read notifications', function () {
        $response = $this->postJson('/api/notifications/invalid-id/read');

        $response->assertUnauthorized();
    });

    test('user cannot mark non-existent notification as read', function () {
        $company = Company::factory()->create();
        $user = User::factory()->has(
            UserProfile::factory()->state(['company_id' => $company->id, 'role' => 'employee'])
        )->create();

        $this->actingAs($user)
            ->postJson('/api/notifications/invalid-id/read')
            ->assertNotFound();
    });
});
