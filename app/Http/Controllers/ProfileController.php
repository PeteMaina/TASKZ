<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Task;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load('workspaces', 'taskNotifications');

        $stats = [
            'workspaces' => $user->workspaces->count(),
            'assigned' => Task::whereHas('assignees', fn ($query) => $query->whereKey($user->id))->count(),
            'unread' => $user->taskNotifications()->whereNull('read_at')->count(),
        ];

        return view('profile.show', compact('user', 'stats'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:60'],
            'theme' => ['required', 'in:light,dark,system'],
            'default_view' => ['required', 'in:board,list,my_tasks'],
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'timezone' => $data['timezone'],
            'preferences' => [
                'theme' => $data['theme'],
                'default_view' => $data['default_view'],
            ],
        ]);

        return back()->with('status', 'Profile updated.');
    }
}
