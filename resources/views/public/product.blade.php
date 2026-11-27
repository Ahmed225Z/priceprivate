@php
    $brand = $merchant->brand_color ?: '#0f766e';
    [$fState, $fLabel] = \App\Support\PriceFormatter::freshness($product->price_updated_at);
    $wa = \App\Support\PriceFormatter::whatsappUrl($merchant->whatsapp, $product->name, $product->publicUrl());
@endphp
@extends('public.layout')
@section('title', $product->name.' - '.$merchant->store_name)
@section('content')
    <header class="head">
        <a href="{{ $merchant->publicUrl() }}">{{ $merchant->store_name }}</a>
        @if ($merchant->is_verified)<span class="chip ok">موثّق</span>@endif
    </header>

    @if ($product->image_path)
        <img class="img" src="{{ asset('storage/'.$product->image_path) }}" width="640" height="480" loading="lazy" alt="{{ $product->name }}">
    @endif

    <h1>{{ $product->name }}</h1>

    @if ($showPrice)
        @if ($product->price !== null)
            <div class="price">{{ \App\Support\PriceFormatter::format($product->price) }} <small>جنيه@if ($product->unit) / {{ $product->unit }}@endif</small></div>
            <span class="chip {{ $fState }}">{{ $fLabel }}</span>
            @if ($product->price_updated_at)
                <div class="muted">آخر تحديث: {{ $product->price_updated_at->translatedFormat('l j F، g:i A') }}</div>
            @endif
        @else
            <div class="price"><small>السعر غير متاح حاليًا</small></div>
        @endif
    @else
        <div class="notice">المنتج ده مش متاح دلوقتي</div>
    @endif

    @if ($wa)<a class="btn wa" href="{{ $wa }}" rel="noopener">واتساب</a>@endif
    @if ($merchant->phone)<a class="btn" href="tel:{{ $merchant->phone }}">اتصال</a>@endif

    <a class="btn outline" href="{{ $merchant->publicUrl() }}">شوف باقي قائمة الأسعار</a>

    @if ($related->isNotEmpty())
        <h2 style="font-size:16px">منتجات من نفس القسم</h2>
        <div class="row">
            @foreach ($related as $p)
                <a href="{{ $p->publicUrl() }}"><div>{{ $p->name }}</div><strong>{{ \App\Support\PriceFormatter::format($p->price) }}</strong></a>
            @endforeach
        </div>
    @endif

    <footer class="muted">
        مدعوم بـ «السعر مش ع الخاص»<br>
        <a href="{{ url('/login') }}">اعمل قائمتك مجانًا</a>
    </footer>
@endsection
