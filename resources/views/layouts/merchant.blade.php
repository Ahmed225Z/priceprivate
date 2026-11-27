<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | لوحة التاجر</title>
    <meta name="robots" content="noindex,nofollow">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body style="background:var(--surface-2)">
    @if (session('impersonator_id'))
        <form method="POST" action="{{ route('admin.impersonate.leave') }}" class="impersonation-bar">
            @csrf أنت داخل كتاجر ({{ auth()->user()?->name }}) <button style="margin-inline-start:8px">رجوع للأدمن</button>
        </form>
    @endif
    <div class="mx-top"><b>{{ auth()->user()->merchant?->store_name }}</b><a class="muted" href="{{ url('/dashboard/subscription') }}">الاشتراك</a></div>
    <div class="mx">
        @if (session('notice'))<div class="notice">{{ session('notice') }}</div>@endif
        @if ($errors->any())<div class="notice" style="background:var(--danger-bg)">{{ $errors->first() }}</div>@endif
        @yield('content')
    </div>
    @php($path = request()->path())
    <nav class="mx-nav" aria-label="التنقل">
        <a href="{{ url('/dashboard') }}" @if ($path === 'dashboard') aria-current="page" @endif><x-ui.icon name="store" :size="22" />الرئيسية</a>
        <a href="{{ url('/dashboard/products') }}" @if (str_starts_with($path, 'dashboard/products')) aria-current="page" @endif><x-ui.icon name="tag" :size="22" />الأسعار</a>
        <a href="{{ url('/dashboard/categories') }}" @if (str_starts_with($path, 'dashboard/categories')) aria-current="page" @endif><x-ui.icon name="menu" :size="22" />الأقسام</a>
        <a href="{{ url('/dashboard/qr') }}" @if (str_starts_with($path, 'dashboard/qr')) aria-current="page" @endif><x-ui.icon name="qr-code" :size="22" />الأكواد</a>
        <a href="{{ url('/dashboard/print') }}" @if (str_starts_with($path, 'dashboard/print')) aria-current="page" @endif><x-ui.icon name="printer" :size="22" />الطباعة</a>
    </nav>
    @stack('scripts')
</body>
</html>
