<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesTaskz;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    use AuthorizesTaskz;

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->ensureWorkspaceAccess($workspace);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:200'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        $project = $workspace->projects()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'client_name' => $data['client_name'] ?? null,
            'color' => $data['color'] ?? '#2563EB',
            'icon' => $data['icon'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('dashboard', ['workspace' => $workspace->uuid, 'project' => $project->uuid]);
    }
}
