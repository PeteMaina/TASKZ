@extends('layouts.app')

@section('body')
<main class="profile-page">
    <nav class="crumbs">
        <a href="{{ route('dashboard', $workspace ? ['workspace' => $workspace->uuid] : []) }}">TASKZ</a>
        <span>Search</span>
    </nav>

    <section class="panel search-page">
        <span class="eyebrow">Command search</span>
        <h1>Find tasks and projects</h1>
        <form method="GET" action="{{ route('search') }}">
            @if ($workspace)<input type="hidden" name="workspace" value="{{ $workspace->uuid }}">@endif
            <input name="q" value="{{ $query }}" placeholder="Type at least two characters" autofocus>
            <button class="button primary">Search</button>
        </form>
    </section>

    <section class="search-results">
        <div class="panel">
            <h2>Tasks</h2>
            @forelse ($tasks as $task)
                <a class="result-row" href="{{ route('dashboard', ['workspace' => $task->project->workspace->uuid, 'project' => $task->project->uuid]).'#task-'.$task->uuid }}">
                    <code>{{ $task->ref() }}</code>
                    <strong>{{ $task->title }}</strong>
                    <span>{{ Str::headline($task->status) }}</span>
                </a>
            @empty
                <p class="muted">No task matches yet.</p>
            @endforelse
        </div>

        <div class="panel">
            <h2>Projects</h2>
            @forelse ($projects as $project)
                <a class="result-row" href="{{ route('dashboard', ['workspace' => $project->workspace->uuid, 'project' => $project->uuid]) }}">
                    <span class="project-dot" style="background: {{ $project->color }}"></span>
                    <strong>{{ $project->name }}</strong>
                    <span>{{ $project->client_name }}</span>
                </a>
            @empty
                <p class="muted">No project matches yet.</p>
            @endforelse
        </div>
    </section>
</main>
@endsection
