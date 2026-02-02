<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\ProjectService;
use App\Services\TeamService;
use App\Services\TicketService;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function __construct(private TicketService $ticketService, private ProjectService $projectService, private TeamService $teamService) {}

    public function index()
    {
        $user = request()->user();
        $tickets = $this->ticketService
            ->queryForUser($user)
            ->with(['project'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('tickets/Index', [
            'tickets' => TicketResource::collection($tickets),
            'canCreateTickets' => $user->can('create', Ticket::class),
        ]);
    }

    public function show(Ticket $ticket)
    {
        return Inertia::render('tickets/Show', [
            'ticket' => new TicketResource($ticket->load(['project', 'creator', 'recipient', 'ticketDetail'])),
        ]);
    }

    public function create()
    {
        $user = request()->user();
        $projects = $this->projectService
            ->queryForUser($user)
            ->get(['id', 'title', 'company_id']);

        $teamMembers = $this->teamService->getTeamMembers($user, ['employee']);

        return Inertia::render('tickets/Create', [
            'projects' => $projects,
            'teamMembers' => $teamMembers,
        ]);
    }

    public function store(StoreTicketRequest $request)
    {
        try {
            $ticket = $this->ticketService->createTicketFromRequest($request, $request->user());

            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['attachment' => 'There was an error processing the attachment. Please try again.']);
        }
    }
}
