<article class="task-card priority-{{ $task->priority }}" id="task-{{ $task->uuid }}">
    <div class="task-card-head">
        <span class="type-badge {{ $task->type }}">{{ $types[$task->type] ?? $task->type }}</span>
        <code>{{ $task->ref() }}</code>
    </div>
    <h3>{{ $task->title }}</h3>
    <p>{{ Str::limit($task->description, 110) }}</p>
    <footer>
        <span>{{ $priorities[$task->priority] }}</span>
        <span>{{ $task->story_points ? $task->story_points.' pts' : 'unestimated' }}</span>
        @if ($task->due_date)<span @class(['danger-text' => $task->due_date->isPast() && $task->status !== 'done'])>{{ $task->due_date->format('M j') }}</span>@endif
        <span>{{ $task->assignees->pluck('name')->join(', ') ?: 'unassigned' }}</span>
    </footer>

    <details class="task-details" data-task-details>
        <summary>Open task</summary>
        <div class="task-dialog">
            <div class="task-dialog-head">
                <div>
                    <span class="type-badge {{ $task->type }}">{{ $types[$task->type] ?? $task->type }}</span>
                    <code>{{ $task->ref() }}</code>
                </div>
                <button type="button" class="text-button" data-close-details>Close</button>
            </div>
        <form method="POST" action="{{ route('tasks.update', $task) }}">
            @csrf
            @method('PUT')
            <input name="title" value="{{ $task->title }}" required>
            <textarea name="description">{{ $task->description }}</textarea>
            <div class="grid-3">
                <select name="type">@foreach ($types as $value => $label)<option value="{{ $value }}" @selected($task->type === $value)>{{ $label }}</option>@endforeach</select>
                <select name="status">@foreach (['open','in_progress','in_review','done','blocked','cancelled'] as $status)<option value="{{ $status }}" @selected($task->status === $status)>{{ Str::headline($status) }}</option>@endforeach</select>
                <select name="priority">@foreach ($priorities as $value => $label)<option value="{{ $value }}" @selected($task->priority === $value)>{{ $label }}</option>@endforeach</select>
            </div>
            <div class="grid-3">
                <select name="story_points"><option value="">Points</option>@foreach ([1,2,3,5,8,13,21] as $point)<option value="{{ $point }}" @selected($task->story_points === $point)>{{ $point }}</option>@endforeach</select>
                <input name="due_date" type="date" value="{{ $task->due_date?->format('Y-m-d') }}">
                <select name="sprint_id"><option value="">Backlog</option>@foreach ($sprints as $sprint)<option value="{{ $sprint->id }}" @selected($task->sprint_id === $sprint->id)>{{ $sprint->name }}</option>@endforeach</select>
            </div>
            <select name="milestone_id"><option value="">No milestone</option>@foreach ($milestones as $milestone)<option value="{{ $milestone->id }}" @selected($task->milestone_id === $milestone->id)>{{ $milestone->name }}</option>@endforeach</select>
            <label class="check-row"><input type="checkbox" name="is_client_facing" value="1" @checked($task->is_client_facing)> Client-facing</label>
            <textarea name="client_note" placeholder="Client note">{{ $task->client_note }}</textarea>
            <button class="button primary">Save task</button>
        </form>

        <section class="dependency-box">
            <h4>Blocked by</h4>
            @forelse ($task->blockedBy as $dependency)
                <form class="dependency-row" method="POST" action="{{ route('dependencies.destroy', [$task, $dependency]) }}">
                    @csrf
                    @method('DELETE')
                    <span>{{ $dependency->ref() }} {{ $dependency->title }}</span>
                    <button class="text-button">Remove</button>
                </form>
            @empty
                <p class="muted">No blockers.</p>
            @endforelse
            <form method="POST" action="{{ route('dependencies.store', $task) }}">
                @csrf
                <select name="blocked_by_id" required>
                    <option value="">Add blocker</option>
                    @foreach ($tasks->where('id', '!=', $task->id) as $candidate)
                        <option value="{{ $candidate->id }}">{{ $candidate->ref() }} {{ $candidate->title }}</option>
                    @endforeach
                </select>
                <button class="button secondary">Add dependency</button>
            </form>
        </section>

        <form method="POST" action="{{ route('tasks.assign', $task) }}">
            @csrf
            <button class="button secondary">Assign to me</button>
        </form>

        <div class="comments">
            <h4>Comments</h4>
            @foreach ($task->comments as $comment)
                <p><strong>{{ $comment->user->name }}</strong> {{ $comment->body }}</p>
            @endforeach
            <form method="POST" action="{{ route('comments.store', $task) }}">
                @csrf
                <textarea name="body" placeholder="Add a comment" required></textarea>
                <button class="button secondary">Comment</button>
            </form>
        </div>
        </div>
    </details>
</article>
