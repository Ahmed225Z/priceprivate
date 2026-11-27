@extends('marketing.layout')
@section('title', 'أمثلة')
@section('body')
<section class="section"><div class="wm" aria-hidden="true">أمثلة</div><div class="wrap-wide">
    <h2>قوائم <em>حقيقية</em></h2>
    @forelse ($stores as $s)
        <a class="card" href="{{ $s->publicUrl() }}" style="display:flex;justify-content:space-between;text-decoration:none;color:inherit;margin-bottom:var(--s-3)">
            <b style="color:var(--ink)">{{ $s->store_name }}</b><span class="muted">{{ $s->products_count }} منتج</span>
        </a>
    @empty
        <div class="empty">لسه مفيش قوائم تتعرض هنا. أول ما التجار ينشروا قوائمهم هتظهر.</div>
    @endforelse
</div></section>
@endsection
