@extends('public.layout')
@section('title', 'لوحة التاجر')
@section('content')
    <h1>{{ $merchant->store_name }}</h1>
    <p class="muted">رابط قائمتك: {{ $merchant->publicUrl() }}</p>
    @if ($remaining !== null)<p class="muted">متاح لك {{ $remaining }} منتج كمان.</p>@endif
    <div class="list">
        @forelse ($products as $p)
            <a href="{{ $p->publicUrl() }}"><span>{{ $p->name }}</span><strong>{{ $p->price !== null ? $p->price : 'غير متاح' }}</strong></a>
        @empty
            <p class="muted">لسه مفيش منتجات.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn outline" style="width:100%">خروج</button></form>
@endsection
