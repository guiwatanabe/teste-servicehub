<?php

namespace App\Http\Controllers;

use App\Services\TeamService;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function __construct(public TeamService $teamService) {}

    public function index()
    {
        $teamMembers = $this->teamService->getTeamMembers(request()->user());

        return Inertia::render('team/Index', ['teamMembers' => $teamMembers]);
    }
}
