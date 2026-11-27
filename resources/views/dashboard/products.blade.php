@extends('layouts.merchant')
@section('title', 'الأسعار')
@section('content')
    <h1>الأسعار</h1>
    <div class="pe-search"><input class="input" id="pe-q" type="search" placeholder="دوّر على منتج" aria-label="بحث"></div>

    <form method="POST" action="{{ url('/dashboard/products/prices') }}" id="pe-form">
        @csrf
        @php($groups = $products->groupBy(fn ($p) => $p->category?->name ?? 'بدون قسم'))
        @forelse ($groups as $cat => $items)
            <div class="pe-group">
                <div class="pe-cat">{{ $cat }} <span class="muted">({{ $items->count() }})</span></div>
                <div class="pe-list">
                    @foreach ($items as $p)
                        <div class="pe-row" data-id="{{ $p->id }}" data-name="{{ $p->name }}">
                            <span class="handle" aria-hidden="true">⋮⋮</span>
                            <span class="nm">{{ $p->name }}@if (! $p->is_visible) <span class="chip warn">مخفي</span>@endif</span>
                            <input name="prices[{{ $p->id }}]" inputmode="decimal" value="{{ $p->price }}" data-orig="{{ $p->price }}" placeholder="السعر" aria-label="سعر {{ $p->name }}">
                            <div class="acts">
                                <button type="submit" form="t-{{ $p->id }}">{{ $p->is_visible ? 'إخفاء' : 'إظهار' }}</button>
                                <a href="{{ url('/qr/'.$p->public_code.'.png') }}" download>تحميل QR</a>
                                <button type="submit" form="d-{{ $p->id }}" onclick="return confirm('تمسح {{ $p->name }}؟')">حذف</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty">لسه مفيش منتجات. ضيف أول منتج من تحت.</div>
        @endforelse
        <div class="savebar"><button class="btn" id="pe-save" type="submit">حفظ كل الأسعار <span id="pe-count"></span></button></div>
    </form>

    @foreach ($products as $p)
        <form id="t-{{ $p->id }}" method="POST" action="{{ url('/dashboard/products/'.$p->public_code.'/toggle') }}">@csrf</form>
        <form id="d-{{ $p->id }}" method="POST" action="{{ url('/dashboard/products/'.$p->public_code) }}">@csrf @method('DELETE')</form>
    @endforeach

    <h2 style="font-size:18px;margin-top:var(--s-8)">منتج جديد</h2>
    <form class="card" method="POST" action="{{ url('/dashboard/products') }}" enctype="multipart/form-data">
        @csrf
        <label class="field">اسم المنتج<input name="name" required></label>
        <label class="field">السعر<input name="price" inputmode="decimal"></label>
        <label class="field">الوحدة<input name="unit" placeholder="كيلو، لتر..."></label>
        <label class="field">الصورة<input type="file" name="image" accept="image/*"></label>
        <label class="field">القسم
            <select name="category_id"><option value="">بدون قسم</option>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
        </label>
        <button class="btn">إضافة</button>
    </form>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        (function () {
            var count = document.getElementById('pe-count');
            function refresh() {
                var n = document.querySelectorAll('.pe-row.changed').length;
                count.textContent = n ? '(' + n + ' اتغيّر)' : '';
            }
            document.querySelectorAll('.pe-row input').forEach(function (i) {
                i.addEventListener('input', function () {
                    i.closest('.pe-row').classList.toggle('changed', i.value !== i.dataset.orig);
                    refresh();
                });
            });
            document.getElementById('pe-q').addEventListener('input', function (e) {
                var q = e.target.value.trim();
                document.querySelectorAll('.pe-row').forEach(function (r) { r.hidden = q && r.dataset.name.indexOf(q) === -1; });
            });
            if (window.Sortable) {
                document.querySelectorAll('.pe-list').forEach(function (el) {
                    Sortable.create(el, { animation: 150, handle: '.handle', onEnd: function () {
                        var ids = Array.from(document.querySelectorAll('.pe-row')).map(function (e) { return e.dataset.id; });
                        fetch('{{ url('/dashboard/products/reorder') }}', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}, body: JSON.stringify({ids: ids}) });
                    }});
                });
            }
        })();
    </script>
    @endpush
@endsection
