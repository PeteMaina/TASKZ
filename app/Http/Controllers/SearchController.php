<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q'));
        $workspace = $request->query('workspace')
            ? Workspace::where('uuid', $request->query('workspace'))->first()
            : $request->user()->workspaces()->first();

        $tasks = collect();
        $projects = collect();

        if ($workspace && strlen($query) >= 2) {
            $projectIds = $workspace->projects()->pluck('id');
            $tasks = Task::with('project')
                ->whereIn('project_id', $projectIds)
                ->where(fn ($builder) => $builder
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%"))
                ->limit(25)
                ->get();

            $projects = Project::where('workspace_id', $workspace->id)
                ->where('name', 'like', "%{$query}%")
                ->limit(10)
                ->get();
        }

        return view('search.index', compact('query', 'workspace', 'tasks', 'projects'));
    }
}
