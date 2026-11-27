<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | السعر مش ع الخاص</title>
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="description" content="@yield('description', 'اعمل قائمة أسعارك واطبع كود QR. الزبون يمسح الكود ويشوف سعرك فورًا، من غير تطبيق ولا تسجيل.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title') | السعر مش ع الخاص">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_EG">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body>
<header class="m-header">
    <div class="wrap-wide bar">
        <a class="m-brand" href="{{ url('/') }}">السعر <span>مش ع الخاص</span></a>
        <nav class="m-nav" aria-label="القائمة الرئيسية">
            <a href="{{ url('/how-it-works') }}">إزاي بتشتغل</a>
            <a href="{{ url('/pricing') }}">الأسعار</a>
            <a href="{{ url('/examples') }}">أمثلة</a>
            <a href="{{ url('/faq') }}">أسئلة شائعة</a>
        </nav>
        <span class="m-spacer"></span>
        <a class="m-btn sm" href="{{ url('/login') }}">ابدأ مجانًا</a>
        <details class="m-menu">
            <summary aria-label="القائمة"><x-ui.icon name="menu" /></summary>
            <div class="panel">
                <a href="{{ url('/how-it-works') }}">إزاي بتشتغل</a>
                <a href="{{ url('/pricing') }}">الأسعار</a>
                <a href="{{ url('/examples') }}">أمثلة</a>
                <a href="{{ url('/faq') }}">أسئلة شائعة</a>
                <a href="{{ url('/contact') }}">تواصل</a>
            </div>
        </details>
    </div>
</header>
<main class="fade-in">
    @yield('body')
</main>
<footer class="m-footer">
    <div class="wrap-wide cols">
        <div>
            <b style="color:var(--ink)">السعر مش ع الخاص</b>
            <p>قائمة أسعار على موبايل الزبون. اعملها مرة، وغيّر السعر من موبايلك في ثواني.</p>
        </div>
        <div>
            <a href="{{ url('/how-it-works') }}">إزاي بتشتغل</a>
            <a href="{{ url('/pricing') }}">الأسعار</a>
            <a href="{{ url('/examples') }}">أمثلة</a>
            <a href="{{ url('/faq') }}">أسئلة شائعة</a>
        </div>
        <div>
            <a href="{{ url('/contact') }}">تواصل</a>
            <a href="{{ url('/terms') }}">الشروط</a>
            <a href="{{ url('/privacy') }}">الخصوصية</a>
            <a href="{{ url('/report') }}">بلّغ عن محتوى</a>
        </div>
    </div>
</footer>
<div class="cookie" id="cookie" hidden>
    <span>الموقع بيستخدم كوكيز ضرورية لتسجيل الدخول وقياس عدد الزيارات بدون تتبع شخصي. <a href="{{ url('/privacy') }}" style="color:var(--brand)">التفاصيل</a></span>
    <button class="m-btn sm" id="cookie-ok" type="button">تمام</button>
</div>
<script>
    (function () {
        var b = document.getElementById('cookie');
        try { if (!localStorage.getItem('cookie_ok')) b.hidden = false; } catch (e) { b.hidden = false; }
        document.getElementById('cookie-ok').addEventListener('click', function () {
            try { localStorage.setItem('cookie_ok', '1'); } catch (e) {}
            b.hidden = true;
        });
    })();
</script>
</body>
</html>
