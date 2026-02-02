<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a project and has many ticket details', function () {
    $ticket = Ticket::factory()->create();

    $creator = User::factory()->create();
    $recipient = User::factory()->create();

    Ticket::factory()->create([
        'project_id' => $ticket->project_id,
        'created_by' => $creator->id,
        'assigned_to' => $recipient->id,
    ]);

    Ticket::factory()->create([
        'project_id' => $ticket->project_id,
        'created_by' => $creator->id,
        'assigned_to' => $recipient->id,
    ]);

    expect($ticket->project)->toBeInstanceOf(Project::class);
});

it('filters tickets by project scope', function () {
    $projectA = Project::factory()->create();
    $projectB = Project::factory()->create();

    Ticket::factory()->count(2)->create(['project_id' => $projectA->id]);
    Ticket::factory()->create(['project_id' => $projectB->id]);

    $tickets = Ticket::withProject($projectA->id)->get();

    expect($tickets)->toHaveCount(2)
        ->and($tickets->pluck('project_id')->unique()->values()->all())
        ->toBe([$projectA->id]);
});

it('filters tickets by creator scope', function () {
    $creatorA = User::factory()->create();
    $creatorB = User::factory()->create();

    Ticket::factory()->count(2)->create(['created_by' => $creatorA->id]);
    Ticket::factory()->create(['created_by' => $creatorB->id]);

    $tickets = Ticket::withCreator($creatorA->id)->get();

    expect($tickets)->toHaveCount(2)
        ->and($tickets->pluck('created_by')->unique()->values()->all())
        ->toBe([$creatorA->id]);
});

it('filters tickets by recipient scope', function () {
    $recipientA = User::factory()->create();
    $recipientB = User::factory()->create();

    Ticket::factory()->count(2)->create(['assigned_to' => $recipientA->id]);
    Ticket::factory()->create(['assigned_to' => $recipientB->id]);

    $tickets = Ticket::withRecipient($recipientA->id)->get();

    expect($tickets)->toHaveCount(2)
        ->and($tickets->pluck('assigned_to')->unique()->values()->all())
        ->toBe([$recipientA->id]);
});

it('filters tickets by company scope', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $projectA = Project::factory()->create(['company_id' => $companyA->id]);
    $projectB = Project::factory()->create(['company_id' => $companyB->id]);

    Ticket::factory()->count(2)->create(['project_id' => $projectA->id]);
    Ticket::factory()->create(['project_id' => $projectB->id]);

    $tickets = Ticket::withCompany($companyA->id)->get();

    expect($tickets)->toHaveCount(2)
        ->and($tickets->pluck('project_id')->unique()->values()->all())
        ->toBe([$projectA->id]);
});

it('has ticket details', function () {
    $ticket = Ticket::factory()->create();
    TicketDetail::factory()->create(['ticket_id' => $ticket->id]);

    expect($ticket->ticketDetail)->toBeInstanceOf(TicketDetail::class);
});
