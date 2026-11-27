<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])
</head>
<body style="font-family: Tajawal, sans-serif; margin:0; min-height:100vh; display:grid; place-items:center; text-align:center; padding:1rem;">
    <main>
        <p style="font-size:4rem; margin:0; font-weight:700;">@yield('code')</p>
        <h1 style="font-size:1.5rem; margin:.5rem 0;">@yield('heading')</h1>
        <p style="margin:0 0 1.5rem;">@yield('message')</p>
        <a href="{{ url('/') }}">الرجوع للرئيسية</a>
    </main>
</body>
</html>
