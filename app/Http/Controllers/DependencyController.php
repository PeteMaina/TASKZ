<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DependencyController extends Controller
{
    use AuthorizesTaskz;

    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->ensureProjectAccess($task->project);

        $data = $request->validate([
            'blocked_by_id' => ['required', 'exists:tasks,id'],
        ]);

        if ((int) $data['blocked_by_id'] === $task->id) {
            return back()->withErrors(['dependency' => 'A task cannot block itself.']);
        }

        $blockedBy = Task::whereKey($data['blocked_by_id'])->where('project_id', $task->project_id)->firstOrFail();

        if ($this->wouldCreateCycle($task, $blockedBy)) {
            return back()->withErrors(['dependency' => 'That dependency would create a cycle.']);
        }

        $task->blockedBy()->syncWithoutDetaching([$blockedBy->id => ['created_by' => $request->user()->id]]);

        return back();
    }

    public function destroy(Task $task, Task $dependency): RedirectResponse
    {
        $this->ensureProjectAccess($task->project);
        $task->blockedBy()->detach($dependency->id);

        return back();
    }

    private function wouldCreateCycle(Task $task, Task $blockedBy): bool
    {
        $seen = [];
        $stack = [$blockedBy->id];

        while ($stack) {
            $id = array_pop($stack);
            if ($id === $task->id) {
                return true;
            }

            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $next = Task::find($id)?->blockedBy()->pluck('tasks.id')->all() ?? [];
            array_push($stack, ...$next);
        }

        return false;
    }
}
