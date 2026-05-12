@extends('layouts.app')

@section('body')
<main class="profile-page">
    <nav class="crumbs">
        <a href="{{ route('dashboard') }}">TASKZ</a>
        <span>Profile</span>
    </nav>

    <section class="profile-hero">
        <div class="avatar-mark">{{ Str::of($user->name)->substr(0, 1)->upper() }}</div>
        <div>
            <span class="eyebrow">Your workspace identity</span>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }} · {{ $user->timezone }}</p>
        </div>
    </section>

    <section class="profile-grid">
        <form class="panel" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <h2>Profile</h2>
            @if (session('status'))<p class="success-text">{{ session('status') }}</p>@endif
            <label>Name <input name="name" value="{{ old('name', $user->name) }}" required></label>
            <label>Timezone <input name="timezone" value="{{ old('timezone', $user->timezone) }}" required></label>
            <label>Theme
                <select name="theme">
                    @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                        <option value="{{ $value }}" @selected(data_get($user->preferences, 'theme', 'light') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Default view
                <select name="default_view">
                    @foreach (['board' => 'Board', 'list' => 'List', 'my_tasks' => 'My Tasks'] as $value => $label)
                        <option value="{{ $value }}" @selected(data_get($user->preferences, 'default_view', 'board') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button primary">Update profile</button>
        </form>

        <aside class="profile-aside">
            <section class="panel stat-panel">
                <h2>Snapshot</h2>
                <dl>
                    <div><dt>Workspaces</dt><dd>{{ $stats['workspaces'] }}</dd></div>
                    <div><dt>Assigned tasks</dt><dd>{{ $stats['assigned'] }}</dd></div>
                    <div><dt>Unread notifications</dt><dd>{{ $stats['unread'] }}</dd></div>
                </dl>
            </section>

            <section class="panel">
                <h2>Recent Notifications</h2>
                @forelse ($user->taskNotifications->sortByDesc('created_at')->take(8) as $notification)
                    <article class="mini-card">
                        <strong>{{ Str::headline($notification->type) }}</strong>
                        <span>{{ $notification->data['ref'] ?? 'TASKZ' }}</span>
                    </article>
                @empty
                    <p class="muted">Nothing has landed here yet.</p>
                @endforelse
            </section>
        </aside>
    </section>
</main>
@endsection
