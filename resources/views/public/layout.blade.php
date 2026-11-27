<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <meta name="robots" content="@yield('robots', 'index,follow')">
    @hasSection('description')<meta name="description" content="@yield('description')">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <style>
        :root { --brand: {{ $brand ?? '#0f766e' }}; --ink:#111827; --muted:#6b7280; --line:#e5e7eb; --ok:#15803d; --warn:#b45309; }
        *{box-sizing:border-box} body{margin:0;font-family:Tajawal,system-ui,sans-serif;color:var(--ink);background:#fff}
        .wrap{max-width:480px;margin:0 auto;padding:12px}
        .head{display:flex;gap:8px;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)}
        .head a{color:inherit;text-decoration:none;font-weight:700}
        .img{width:100%;aspect-ratio:4/3;object-fit:cover;background:#f3f4f6;border-radius:8px;display:block;margin:12px 0}
        h1{font-size:22px;margin:8px 0}
        .price{font-size:40px;font-weight:700;color:var(--brand);margin:8px 0}
        .price small{font-size:16px;color:var(--muted);font-weight:400}
        .chip{display:inline-block;padding:2px 10px;border-radius:99px;font-size:13px;border:1px solid var(--line)}
        .chip.ok{color:var(--ok)} .chip.warn{color:var(--warn)}
        .muted{color:var(--muted);font-size:12px}
        .btn{display:block;text-align:center;padding:12px;border-radius:8px;margin:8px 0;text-decoration:none;font-weight:700;color:#fff;background:var(--brand)}
        .btn.wa{background:#16a34a} .btn.outline{background:#fff;color:var(--brand);border:1px solid var(--brand)}
        .notice{background:#fef3c7;padding:10px;border-radius:8px;margin:8px 0}
        .row{display:flex;gap:8px;overflow-x:auto;padding:4px 0}
        .row a{flex:0 0 140px;border:1px solid var(--line);border-radius:8px;padding:8px;color:inherit;text-decoration:none}
        .list a{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--line);color:inherit;text-decoration:none}
        footer{margin-top:24px;text-align:center}
    </style>
</head>
<body>
<div class="wrap">
    @if (session('notice'))<div class="notice">{{ session('notice') }}</div>@endif
    @yield('content')
</div>
</body>
</html>
