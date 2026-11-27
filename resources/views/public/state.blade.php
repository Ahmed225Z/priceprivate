@extends('public.layout')
@section('title', $title)
@section('robots', 'noindex')
@section('content')
    <main style="text-align:center;padding-top:20vh">
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a class="btn outline" href="{{ url('/') }}">الصفحة الرئيسية</a>
        @if (!empty($cta))<a class="btn" href="{{ url('/login') }}">اعمل قائمتك مجانًا</a>@endif
    </main>
@endsection
