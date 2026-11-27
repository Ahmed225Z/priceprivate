@extends('public.layout')
@section('title', 'لوحة التاجر')
@section('content')
    <h1>{{ $merchant->store_name }}</h1>
    <p class="muted">رابط قائمتك: {{ $merchant->publicUrl() }}</p>
    @if ($remaining !== null)<p class="muted">متاح لك {{ $remaining }} منتج كمان.</p>@endif
    <button class="btn" id="share-store" style="width:100%;border:0;margin:8px 0">شارك رابط المحل</button>
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
    <div class="list">
        @forelse ($products as $p)
            <a href="{{ $p->publicUrl() }}"><span>{{ $p->name }}</span><strong>{{ $p->price !== null ? $p->price : 'غير متاح' }}</strong></a>
        @empty
            <p class="muted">لسه مفيش منتجات.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn outline" style="width:100%">خروج</button></form>
@endsection
