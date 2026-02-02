<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Services\TicketService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(public TicketService $ticketService) {}

    public function index()
    {
        $user = request()->user();
        /** @var \App\Models\UserProfile $userProfile */
        $userProfile = $user->userProfile;

        $recentTickets = $this->ticketService
            ->queryForUser($user)
            ->with(['project:id,title,company_id', 'project.company:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $statusCounts = $this->ticketService->getTicketStatusCounts(request()->user());

        $data = [
            'tickets' => TicketResource::collection($recentTickets),
            'statusCounts' => $statusCounts,
        ];

        if ($userProfile->role === 'admin' || $userProfile->role === 'manager') {
            $companyId = $userProfile->company_id;
            $data['totalProjects'] = Project::withCompany($companyId)->count();
            $data['totalTickets'] = Ticket::withCompany($companyId)->count();
        }

        return match ($userProfile->role) {
            'admin' => Inertia::render('dashboard/AdminDashboard', $data),
            'manager' => Inertia::render('dashboard/ManagerDashboard', $data),
            'employee' => Inertia::render('dashboard/EmployeeDashboard', $data)
        };
    }
}
