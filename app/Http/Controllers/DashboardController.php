<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AuthorizesTaskz;

    public function __invoke(Request $request): View
    {
        $workspaces = $request->user()
            ->workspaces()
            ->with(['projects' => fn ($query) => $query->where('status', 'active')->latest()])
            ->latest('workspace_members.joined_at')
            ->get();

        $workspace = $request->query('workspace')
            ? Workspace::where('uuid', $request->query('workspace'))->firstOrFail()
            : $workspaces->first();

        if ($workspace) {
            $this->ensureWorkspaceAccess($workspace);
        }

        $project = $request->query('project')
            ? Project::with('workspace')->where('uuid', $request->query('project'))->firstOrFail()
            : $workspace?->projects()->where('status', 'active')->first();

        if ($project) {
            $this->ensureProjectAccess($project);
        }

        $tasks = collect();
        $sprints = collect();
        $milestones = collect();

        if ($project) {
            $tasks = $project->tasks()
                ->with(['project', 'sprint', 'milestone', 'assignees', 'labels', 'comments.user', 'blockedBy.project', 'blocking.project'])
                ->when($request->filled('search'), fn ($query) => $query->whereFullText(['title', 'description'], $request->query('search')))
                ->orderBy('position')
                ->latest()
                ->get();

            $sprints = $project->sprints()->latest()->get();
            $milestones = $project->milestones()->latest()->get();
        }

        $myTasks = Task::query()
            ->with(['project', 'sprint', 'milestone'])
            ->whereHas('assignees', fn ($query) => $query->whereKey($request->user()->id))
            ->where('status', '!=', 'done')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->get();

        return view('dashboard', compact('workspaces', 'workspace', 'project', 'tasks', 'sprints', 'milestones', 'myTasks'));
    }
}
