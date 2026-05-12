<!doctype html>
<html lang="en" data-theme="{{ data_get(auth()->user()?->preferences, 'theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TASKZ') }}</title>
    <link rel="stylesheet" href="{{ rtrim(request()->getBaseUrl(), '/') }}/css/taskz.css">
    <script src="{{ rtrim(request()->getBaseUrl(), '/') }}/js/taskz.js" defer></script>
</head>
<body>
    @yield('body')
    <aside class="shortcut-reference">
        <strong>Shortcuts</strong>
        <span>Ctrl+K opens search</span>
        <span>? toggles this panel</span>
        <span>Esc closes dialogs</span>
    </aside>
</body>
</html>
