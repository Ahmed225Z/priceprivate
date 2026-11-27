@extends('marketing.layout')
@section('title', 'الخطط والأسعار')
@section('body')
    <h1>الخطط</h1>
    @foreach ($plans as $plan)
        <div style="border:1px solid var(--line);border-radius:8px;padding:12px;margin:10px 0">
            <h2 style="margin:0">{{ $plan->name }}@if ($plan->code === 'pro') <span class="chip ok">الأكثر طلبًا</span>@endif</h2>
            <div class="price" style="font-size:28px">{{ \App\Support\PriceFormatter::format((string) $plan->price) }} <small>{{ $plan->currency === 'EGP' ? 'جنيه' : $plan->currency }}@if ($plan->duration_days) / سنة@endif</small></div>
            <p>{{ $plan->product_limit === null ? 'منتجات غير محدودة' : 'لحد '.$plan->product_limit.' منتج' }}</p>
        </div>
    @endforeach
    <a class="btn" href="{{ url('/login') }}">ابدأ دلوقتي</a>
@endsection
