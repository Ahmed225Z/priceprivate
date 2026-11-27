@extends('layouts.merchant')
@section('title', 'الرئيسية')
@section('content')
    <h1>أهلًا يا {{ $merchant->store_name }}</h1>
    <p class="muted">رابط قائمتك: <span dir="ltr">{{ $merchant->publicUrl() }}</span></p>
    <button class="btn" id="share-store" type="button">شارك رابط المحل</button>
    <div class="stats">
        <div class="stat"><div class="n">{{ $products->count() }}</div><div class="l">منتج</div></div>
        <div class="stat"><div class="n">{{ $remaining === null ? 'غير محدود' : $remaining }}</div><div class="l">{{ $remaining === null ? 'حد المنتجات' : 'منتج كمان متاح' }}</div></div>
        <div class="stat {{ $stale > 0 ? 'warn' : '' }}"><div class="n">{{ $stale }}</div><div class="l">سعر ما اتحدّثش من 7 أيام</div></div>
        <div class="stat"><div class="n">{{ $scansToday }}</div><div class="l">مسح النهارده</div></div>
    </div>
    @if ($stale > 0)<a class="btn outline" href="{{ url('/dashboard/products') }}">حدّث الأسعار القديمة</a>@endif
    <h2 style="font-size:18px">آخر المنتجات</h2>
    <div class="list card flat" style="padding-block:0">
        @forelse ($products->take(8) as $p)
            <a href="{{ $p->publicUrl() }}"><span>{{ $p->name }}</span><strong class="tnum">{{ $p->price !== null ? $p->price : 'غير متاح' }}</strong></a>
        @empty
            <div class="empty" style="border:0">لسه مفيش منتجات. ابدأ من «الأسعار».</div>
        @endforelse
    </div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn outline">خروج</button></form>
    @push('scripts')
    <script>
        document.getElementById('share-store').addEventListener('click', async function () {
            var url = @json($merchant->publicUrl());
            try {
                if (navigator.share) { await navigator.share({title: @json($merchant->store_name), url: url}); }
                else { await navigator.clipboard.writeText(url); alert('اتنسخ الرابط'); }
            } catch (e) { return; }
            fetch('{{ url('/dashboard/share') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}, body: JSON.stringify({target: 'store'})});
        });
    </script>
    @endpush
@endsection
