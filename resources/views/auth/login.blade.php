@extends('layouts.app')

@section('body')
<main class="auth-layout">
    <section class="auth-copy">
        <span class="eyebrow">TASKZ</span>
        <h1>Developer's workspace, client commitments.</h1>
        <p>Log in, create a workspace, create a project, then run the sprint loops and more..</p>
    </section>

    <form class="auth-card" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div>
            <h2>Welcome back</h2>
            <p>Use your TASKZ account.</p>
        </div>
        <label>Email <input name="email" type="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password <input name="password" type="password" required></label>
        <label class="check-row"><input name="remember" type="checkbox" value="1"> Remember me</label>
        @if ($errors->any())
            <p class="form-error">{{ $errors->first() }}</p>
        @endif
        <button class="button primary">Log in</button>
        <a class="text-button" href="{{ route('register') }}">Need an account?</a>
    </form>
</main>
@endsection
