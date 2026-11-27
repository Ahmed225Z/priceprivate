@extends('public.layout')
@section('content')
    <header class="head">
        <a href="{{ url('/') }}">السعر مش ع الخاص</a>
        <span style="flex:1"></span>
        <a href="{{ url('/pricing') }}">الأسعار</a>
        <a href="{{ url('/how-it-works') }}">إزاي بتشتغل</a>
        <a href="{{ url('/login') }}">دخول</a>
    </header>
    @yield('body')
    <footer class="muted">
        <a href="{{ url('/faq') }}">أسئلة شائعة</a> ·
        <a href="{{ url('/terms') }}">الشروط</a> ·
        <a href="{{ url('/privacy') }}">الخصوصية</a>
    </footer>
@endsection
