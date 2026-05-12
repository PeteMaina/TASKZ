@extends('layouts.app')

@section('body')
<main class="auth-layout">
    <section class="auth-copy">
        <span class="eyebrow">TASKZ</span>
        <h1>Register your account</h1>
        <p>Manage your tasks better as a developer</p>
    </section>

    <form class="auth-card" method="POST" action="{{ route('register.store') }}">
        @csrf
        <div>
            <h2>Create your account</h2>
            <p>Passwords must be at least 10 characters.</p>
        </div>
        <label>Name <input name="name" value="{{ old('name') }}" required autofocus></label>
        <label>Email <input name="email" type="email" value="{{ old('email') }}" required></label>
        <label>Timezone <input name="timezone" value="{{ old('timezone', 'Africa/Nairobi') }}"></label>
        <label>Password <input name="password" type="password" required></label>
        <label>Confirm password <input name="password_confirmation" type="password" required></label>
        @if ($errors->any())
            <p class="form-error">{{ $errors->first() }}</p>
        @endif
        <button class="button primary">Register</button>
        <a class="text-button" href="{{ route('login') }}">Already have an account?</a>
    </form>
</main>
@endsection
