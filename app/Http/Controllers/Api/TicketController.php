<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloseTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:open,in_progress,closed'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'order_by' => ['sometimes', 'in:created_at,due_date'],
            'order_dir' => ['sometimes', 'in:asc,desc'],
        ]);

        $user = request()->user();
        $perPage = (int) ($validated['per_page'] ?? 10);

        $orderBy = $validated['order_by'] ?? 'created_at';
        $orderDir = $validated['order_dir'] ?? 'desc';

        $tickets = $this->ticketService->getTickets($user, ['project:id,title,company_id', 'project.company:id,name'], $perPage, [
            'status' => $validated['status'] ?? null,
            'priority' => $validated['priority'] ?? null,
        ], $orderBy, $orderDir);

        return TicketResource::collection($tickets);
    }

    public function close(CloseTicketRequest $request, Ticket $ticket)
    {
        $ticket = $this->ticketService->closeTicket($ticket->id);

        return new TicketResource($ticket);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return new TicketResource($ticket->load([
            'project:id,title',
            'creator:id,name,email',
            'recipient:id,name,email',
            'ticketDetail',
        ]));
    }
}
