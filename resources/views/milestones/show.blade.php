@extends('layouts.app')

@section('body')
<main class="profile-page">
    <nav class="crumbs">
        <a href="{{ route('dashboard', ['workspace' => $milestone->project->workspace->uuid, 'project' => $milestone->project->uuid]) }}">Back to project</a>
        <span>{{ $milestone->name }}</span>
    </nav>

    <section class="profile-hero milestone-hero">
        <div>
            <span class="eyebrow">Milestone</span>
            <h1>{{ $milestone->name }}</h1>
            <p>{{ $milestone->description ?: 'No description yet.' }}</p>
        </div>
        <div class="completion-ring">{{ $completion }}%</div>
    </section>

    <section class="panel">
        <h2>Linked Tasks</h2>
        @forelse ($milestone->tasks as $task)
            <article class="result-row">
                <code>{{ $task->ref() }}</code>
                <strong>{{ $task->title }}</strong>
                <span>{{ Str::headline($task->status) }}</span>
            </article>
        @empty
            <p class="muted">No tasks are linked to this milestone yet.</p>
        @endforelse
    </section>
</main>
@endsection
