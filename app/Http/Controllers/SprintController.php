<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    use AuthorizesTaskz;

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->ensureManager($project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'goal' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'velocity_plan' => ['nullable', 'integer', 'min:0'],
        ]);

        $project->sprints()->create([...$data, 'created_by' => $request->user()->id]);

        return back();
    }

    public function activate(Sprint $sprint): RedirectResponse
    {
        $this->ensureManager($sprint->project);

        if ($sprint->project->sprints()->where('status', 'active')->whereKeyNot($sprint->id)->exists()) {
            return back()->withErrors(['sprint' => 'Project already has an active sprint.']);
        }

        $sprint->update(['status' => 'active']);

        return back();
    }

    public function close(Sprint $sprint): RedirectResponse
    {
        $this->ensureManager($sprint->project);

        $velocity = $sprint->tasks()->where('status', 'done')->sum('story_points');
        $sprint->tasks()->where('status', '!=', 'done')->update(['sprint_id' => null]);
        $sprint->update(['status' => 'closed', 'closed_at' => now(), 'velocity_actual' => $velocity]);

        return back();
    }
}
