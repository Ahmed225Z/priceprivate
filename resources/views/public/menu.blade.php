@php $brand = $merchant->brand_color ?: '#0f766e'; @endphp
@extends('public.layout')
@section('title', $merchant->store_name)
@section('content')
    <header class="head"><a href="{{ $merchant->publicUrl() }}">{{ $merchant->store_name }}</a></header>
    @if ($categories->isNotEmpty())
        <div class="row">
            @foreach ($categories as $c)
                <a href="{{ url('/'.$merchant->slug.'/'.$c->slug) }}">{{ $c->name }}</a>
            @endforeach
        </div>
    @endif
    @include('public.ad', ['slot' => $adAfterFirst])
    <div class="list">
        @forelse ($products as $p)
            <a href="{{ $p->publicUrl() }}"><span>{{ $p->name }}</span>
                <strong>{{ $p->price !== null ? \App\Support\PriceFormatter::format($p->price).' جنيه' : 'غير متاح' }}</strong></a>
        @empty
            <p class="muted">مفيش منتجات هنا لسه.</p>
        @endforelse
    </div>
    @include('public.ad', ['slot' => $adFooter])
    @if ($merchant->phone)<a class="btn" href="tel:{{ $merchant->phone }}">اتصال</a>@endif
@endsection
