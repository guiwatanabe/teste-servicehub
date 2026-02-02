<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TicketResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\TicketService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService, private TicketService $ticketService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = $this->projectService
            ->queryForUser(request()->user())
            ->with(['company:id,name'])
            ->get();

        return ProjectResource::collection($projects);
    }

    public function tickets(Request $request, Project $project)
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        /** @var \App\Models\UserProfile $userProfile */
        $userProfile = $user->userProfile;

        if ($project->company_id !== $userProfile->company_id) {
            abort(403);
        }

        $perPage = (int) ($validated['per_page'] ?? 10);

        /** @phpstan-ignore-next-line */
        $tickets = $this->ticketService
            ->queryForUser($user)
            ->withProject($project->id)
            ->with(['project:id,title,company_id', 'project.company:id,name'])
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return TicketResource::collection($tickets);
    }
}
