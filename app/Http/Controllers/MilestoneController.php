<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestoneController extends Controller
{
    use AuthorizesTaskz;

    public function show(Milestone $milestone): View
    {
        $this->ensureProjectAccess($milestone->project);

        $milestone->load(['project.workspace', 'tasks.assignees']);
        $total = $milestone->tasks->count();
        $done = $milestone->tasks->where('status', 'done')->count();
        $completion = $total ? round(($done / $total) * 100) : 0;

        return view('milestones.show', compact('milestone', 'completion'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->ensureManager($project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->milestones()->create([...$data, 'created_by' => $request->user()->id]);

        return back();
    }

    public function complete(Milestone $milestone): RedirectResponse
    {
        $this->ensureManager($milestone->project);
        $milestone->update(['status' => 'completed', 'completed_at' => now()]);

        return back();
    }
}
