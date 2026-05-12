@extends('layouts.app')

@php
    $columns = ['open' => 'Open', 'in_progress' => 'In Progress', 'in_review' => 'In Review', 'done' => 'Done'];
    $types = ['task' => 'Task', 'bug' => 'Bug', 'feature' => 'Feature', 'chore' => 'Chore', 'spike' => 'Spike', 'hotfix' => 'Hotfix'];
    $priorities = [0 => 'None', 1 => 'Low', 2 => 'Medium', 3 => 'High', 4 => 'Critical'];
@endphp

@section('body')
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">TASKZ</a>
        <nav class="profile-strip">
            <a href="{{ route('profile.show') }}">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->email }}</span>
            </a>
            <button class="theme-toggle" type="button" data-theme-toggle>Theme</button>
        </nav>

        <section class="side-block">
            <span class="eyebrow">Workspace</span>
            @foreach ($workspaces as $item)
                <a class="side-link @class(['active' => $workspace?->is($item)])" href="{{ route('dashboard', ['workspace' => $item->uuid]) }}">
                    {{ $item->name }}
                </a>
            @endforeach
            <form class="compact-form" method="POST" action="{{ route('workspaces.store') }}">
                @csrf
                <input name="name" placeholder="New workspace" required>
                <button class="button secondary">Create</button>
            </form>
        </section>

        @if ($workspace)
            <section class="side-block">
                <span class="eyebrow">Projects</span>
                @foreach ($workspace->projects as $item)
                    <a class="side-link @class(['active' => $project?->is($item)])" href="{{ route('dashboard', ['workspace' => $workspace->uuid, 'project' => $item->uuid]) }}">
                        <span class="project-dot" style="background: {{ $item->color }}"></span>
                        {{ $item->name }}
                        @if ($item->client_name)<small>{{ $item->client_name }}</small>@endif
                    </a>
                @endforeach
                <form class="compact-form" method="POST" action="{{ route('projects.store', $workspace) }}">
                    @csrf
                    <input name="name" placeholder="New project" required>
                    <input name="client_name" placeholder="Client name">
                    <button class="button secondary">Create</button>
                </form>
            </section>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="side-link">Logout</button>
        </form>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <span class="eyebrow">{{ $workspace?->name ?? 'No workspace yet' }}</span>
                <h1>{{ $project?->name ?? 'Create a project' }}</h1>
            </div>
            @if ($project)
                <div class="top-actions">
                    <form class="search-box" method="GET" action="{{ route('dashboard') }}">
                        <input type="hidden" name="workspace" value="{{ $workspace->uuid }}">
                        <input type="hidden" name="project" value="{{ $project->uuid }}">
                        <input name="search" value="{{ request('search') }}" placeholder="Search tasks">
                    </form>
                    <a class="button secondary" data-command-open href="{{ route('search', ['workspace' => $workspace->uuid]) }}">Command</a>
                    <details class="notifications-menu">
                        <summary>{{ auth()->user()->taskNotifications()->whereNull('read_at')->count() }} unread</summary>
                        @forelse (auth()->user()->taskNotifications()->latest()->limit(5)->get() as $notification)
                            <p>{{ Str::headline($notification->type) }}: {{ $notification->data['ref'] ?? 'TASKZ' }}</p>
                        @empty
                            <p>No notifications yet.</p>
                        @endforelse
                        <form method="POST" action="{{ route('notifications.read') }}">
                            @csrf
                            @method('PATCH')
                            <button class="button secondary">Mark read</button>
                        </form>
                    </details>
                </div>
            @endif
        </header>

        @if ($errors->any())
            <div class="notice">{{ $errors->first() }}</div>
        @endif

        @unless ($workspace)
            <section class="empty-state">
                <h2>Create your first workspace.</h2>
                <form class="inline-create" method="POST" action="{{ route('workspaces.store') }}">
                    @csrf
                    <input name="name" placeholder="Workspace name" required>
                    <button class="button primary">Create your workspace</button>
                </form>
            </section>
        @endunless

        @if ($workspace && ! $project)
            <section class="empty-state">
                <h2>Create your first project.</h2>
                <form class="inline-create" method="POST" action="{{ route('projects.store', $workspace) }}">
                    @csrf
                    <input name="name" placeholder="Project name" required>
                    <input name="client_name" placeholder="Client name">
                    <button class="button primary">Create project</button>
                </form>
            </section>
        @endif

        @if ($project)
            <section class="quick-grid">
                <form class="panel" method="POST" action="{{ route('tasks.store', $project) }}">
                    @csrf
                    <h2>New Task</h2>
                    <input name="title" placeholder="Task title" required>
                    <textarea name="description" placeholder="Description"></textarea>
                    <div class="grid-3">
                        <select name="type">@foreach ($types as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                        <select name="priority">@foreach ($priorities as $value => $label)<option value="{{ $value }}" @selected($value === 2)>{{ $label }}</option>@endforeach</select>
                        <select name="story_points"><option value="">Points</option>@foreach ([1,2,3,5,8,13,21] as $point)<option value="{{ $point }}">{{ $point }}</option>@endforeach</select>
                    </div>
                    <div class="grid-3">
                        <input name="due_date" type="date">
                        <select name="sprint_id"><option value="">Backlog</option>@foreach ($sprints as $sprint)<option value="{{ $sprint->id }}">{{ $sprint->name }}</option>@endforeach</select>
                        <select name="milestone_id"><option value="">No milestone</option>@foreach ($milestones as $milestone)<option value="{{ $milestone->id }}">{{ $milestone->name }}</option>@endforeach</select>
                    </div>
                    <label class="check-row"><input type="checkbox" name="assign_to_me" value="1"> Assign to me</label>
                    <label class="check-row"><input type="checkbox" name="is_client_facing" value="1"> Client-facing</label>
                    <button class="button primary">Create task</button>
                </form>

                <form class="panel" method="POST" action="{{ route('sprints.store', $project) }}">
                    @csrf
                    <h2>New Sprint</h2>
                    <input name="name" placeholder="Sprint name" required>
                    <textarea name="goal" placeholder="Sprint goal"></textarea>
                    <input name="start_date" type="date" required>
                    <input name="end_date" type="date" required>
                    <button class="button secondary">Create sprint</button>
                </form>

                <form class="panel" method="POST" action="{{ route('milestones.store', $project) }}">
                    @csrf
                    <h2>New Milestone</h2>
                    <input name="name" placeholder="Milestone name" required>
                    <input name="due_date" type="date">
                    <button class="button secondary">Create milestone</button>
                </form>
            </section>

            <section class="workspace-grid">
                <div>
                    <div class="section-head">
                        <h2>Project Board</h2>
                        <span>{{ $tasks->count() }} tasks</span>
                    </div>
                    <div class="board">
                        @foreach ($columns as $status => $label)
                            <div class="board-column">
                                <header>{{ $label }} <span>{{ $tasks->where('status', $status)->count() }}</span></header>
                                @foreach ($tasks->where('status', $status) as $task)
                                    @include('partials.task-card', ['task' => $task, 'types' => $types, 'priorities' => $priorities, 'sprints' => $sprints, 'milestones' => $milestones, 'tasks' => $tasks])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="right-rail">
                    <section class="panel">
                        <h2>Sprints</h2>
                        @foreach ($sprints as $sprint)
                            <article class="mini-card">
                                <strong>{{ $sprint->name }}</strong>
                                <span>{{ ucfirst($sprint->status) }}</span>
                                <p>{{ $sprint->goal }}</p>
                                <div class="button-row">
                                    @if ($sprint->status === 'planned')
                                        <form method="POST" action="{{ route('sprints.activate', $sprint) }}">@csrf<button class="button secondary">Activate</button></form>
                                    @endif
                                    @if ($sprint->status === 'active')
                                        <form method="POST" action="{{ route('sprints.close', $sprint) }}">@csrf<button class="button secondary">Close</button></form>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <section class="panel">
                        <h2>Milestones</h2>
                        @foreach ($milestones as $milestone)
                            <article class="mini-card">
                                <a href="{{ route('milestones.show', $milestone) }}"><strong>{{ $milestone->name }}</strong></a>
                                <span>{{ ucfirst($milestone->status) }}</span>
                                @if ($milestone->due_date)<p>Due {{ $milestone->due_date->format('M j, Y') }}</p>@endif
                                @if ($milestone->status === 'open')
                                    <form method="POST" action="{{ route('milestones.complete', $milestone) }}">@csrf<button class="button secondary">Complete</button></form>
                                @endif
                            </article>
                        @endforeach
                    </section>

                    <section class="panel">
                        <h2>My Tasks</h2>
                        @forelse ($myTasks as $task)
                            <article class="mini-card">
                                <strong>{{ $task->title }}</strong>
                                <span>{{ $task->ref() }}</span>
                                @if ($task->due_date)<p>Due {{ $task->due_date->format('M j, Y') }}</p>@endif
                            </article>
                        @empty
                            <p class="muted">Nothing due. Either you're on top of things or no one's assigned you anything.</p>
                        @endforelse
                    </section>
                </aside>
            </section>
        @endif
    </main>
</div>

@if ($workspace)
    <dialog class="command-dialog" data-command-dialog>
        <form method="GET" action="{{ route('search') }}">
            <input type="hidden" name="workspace" value="{{ $workspace->uuid }}">
            <input name="q" placeholder="Search tasks and projects" autofocus>
            <button class="button primary">Search</button>
        </form>
        <button class="text-button" type="button" data-command-close>Close</button>
    </dialog>
@endif
@endsection
