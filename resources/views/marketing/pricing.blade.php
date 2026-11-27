@extends('marketing.layout')
@section('title', 'الخطط والأسعار')
@section('description', 'خطة مجانية وخطة مدفوعة بدون التزام. الأسعار وحدود المنتجات من لوحة الإدارة.')
@section('body')
<section class="section">
    <div class="wm" aria-hidden="true">الخطط</div>
    <div class="wrap-wide">
        <h2>الخطط <em>والأسعار</em></h2>
        @include('marketing._plans', ['plans' => $plans])
        <p class="muted" style="margin-top:var(--s-4);max-width:40em">لو اشتراكك خلص، قائمتك بتفضل شغالة وزبونك يفضل يشوف أسعارك. بنرجع بس للإعلانات واللون الافتراضي، وبنخفي المنتجات اللي فوق حد الخطة المجانية لحد ما تجدّد.</p>
    </div>
</section>
@endsection
