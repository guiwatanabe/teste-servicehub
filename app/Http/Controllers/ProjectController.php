<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index()
    {
        $user = request()->user();
        $projects = $this->projectService
            ->queryForUserWithTicketCounts($user)
            ->with(['company:id,name'])
            ->paginate(10);

        return Inertia::render('projects/Index', ['projects' => ProjectResource::collection($projects)]);
    }

    public function show(Project $project)
    {
        $user = request()->user();
        $project = $this->projectService->loadTicketCountsForUser($user, $project);

        return Inertia::render('projects/Show', [
            'project' => new ProjectResource($project),
        ]);
    }
}
