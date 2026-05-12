<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Task;
use App\Models\TaskNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use AuthorizesTaskz;

    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->ensureProjectAccess($task->project);

        $data = $request->validate(['body' => ['required', 'string']]);
        $task->comments()->create(['body' => $data['body'], 'user_id' => $request->user()->id]);

        foreach ($task->assignees as $assignee) {
            if ($assignee->is($request->user())) {
                continue;
            }

            TaskNotification::create([
                'user_id' => $assignee->id,
                'type' => 'comment_added',
                'data' => ['task' => $task->title, 'ref' => $task->ref()],
            ]);
        }

        return back();
    }
}
