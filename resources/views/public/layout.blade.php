<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <meta name="robots" content="@yield('robots', 'index,follow')">
    @hasSection('description')<meta name="description" content="@yield('description')">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    @isset($brand)<style>:root { --brand: {{ $brand }}; }</style>@endisset
</head>
<body>
    @if (session('impersonator_id'))
        <form method="POST" action="{{ route('admin.impersonate.leave') }}" class="impersonation-bar">
            @csrf أنت داخل كتاجر ({{ auth()->user()?->name }}) <button style="margin-inline-start:8px">رجوع للأدمن</button>
        </form>
    @endif
<div class="wrap">
    @if (session('notice'))<div class="notice">{{ session('notice') }}</div>@endif
    @yield('content')
</div>
</body>
</html>
