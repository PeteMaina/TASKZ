<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    use AuthorizesTaskz;

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->ensureProjectAccess($project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['task', 'bug', 'feature', 'chore', 'spike', 'hotfix'])],
            'priority' => ['required', 'integer', 'between:0,4'],
            'story_points' => ['nullable', 'integer', Rule::in([1, 2, 3, 5, 8, 13, 21])],
            'due_date' => ['nullable', 'date'],
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'is_client_facing' => ['nullable', 'boolean'],
            'client_note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($project, $request, $data) {
            $number = $project->tasks()->lockForUpdate()->max('task_number') + 1;

            $task = $project->tasks()->create([
                ...$data,
                'task_number' => $number,
                'status' => 'open',
                'is_client_facing' => $request->boolean('is_client_facing'),
                'created_by' => $request->user()->id,
            ]);

            if ($request->boolean('assign_to_me')) {
                $task->assignees()->attach($request->user()->id, ['assigned_by' => $request->user()->id]);
            }
        });

        return back();
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->ensureProjectAccess($task->project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['task', 'bug', 'feature', 'chore', 'spike', 'hotfix'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'in_review', 'done', 'cancelled', 'blocked'])],
            'priority' => ['required', 'integer', 'between:0,4'],
            'story_points' => ['nullable', 'integer', Rule::in([1, 2, 3, 5, 8, 13, 21])],
            'due_date' => ['nullable', 'date'],
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'is_client_facing' => ['nullable', 'boolean'],
            'client_note' => ['nullable', 'string'],
        ]);

        $task->fill($data);
        $task->is_client_facing = $request->boolean('is_client_facing');
        $task->completed_at = $data['status'] === 'done' ? now() : null;
        $task->completed_by = $data['status'] === 'done' ? $request->user()->id : null;
        $task->save();

        return back();
    }

    public function assign(Request $request, Task $task): RedirectResponse
    {
        $this->ensureProjectAccess($task->project);
        $task->assignees()->syncWithoutDetaching([$request->user()->id => ['assigned_by' => $request->user()->id]]);
        TaskNotification::create([
            'user_id' => $request->user()->id,
            'type' => 'task_assigned',
            'data' => ['task' => $task->title, 'ref' => $task->ref()],
        ]);

        return back();
    }
}
