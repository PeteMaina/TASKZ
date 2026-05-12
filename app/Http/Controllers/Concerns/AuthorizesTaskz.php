<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

trait AuthorizesTaskz
{
    protected function workspaceRole(Workspace $workspace): string
    {
        $member = $workspace->members()->whereKey(Auth::id())->first();
        abort_unless($member, 404);

        return $member->pivot->role;
    }

    protected function ensureWorkspaceAccess(Workspace $workspace): void
    {
        $this->workspaceRole($workspace);
    }

    protected function ensureProjectAccess(Project $project): void
    {
        $this->ensureWorkspaceAccess($project->workspace);
    }

    protected function ensureManager(Project $project): void
    {
        abort_unless(in_array($this->workspaceRole($project->workspace), ['owner', 'admin'], true), 403);
    }
}
