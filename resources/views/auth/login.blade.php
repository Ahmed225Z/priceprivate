@extends('public.layout')
@section('title', 'تسجيل الدخول')
@section('content')
    <main style="text-align:center;padding-top:15vh">
        <h1>ادخل على قائمتك</h1>
        <a class="btn" href="{{ url('/auth/google/redirect') }}">الدخول بجوجل</a>
        @if ($facebookEnabled)<a class="btn outline" href="{{ url('/auth/facebook/redirect') }}">الدخول بفيسبوك</a>@endif
    </main>
@endsection
