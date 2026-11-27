@extends('layouts.merchant')
@section('title', 'الطباعة')
@section('content')
    <h1>استوديو الطباعة</h1>
    <form class="card" method="POST" action="{{ url('/dashboard/print') }}">@csrf
        <label class="field">القالب
            <select name="template" id="tpl">
                <option value="label_sheet">ملصقات A4 (24 ملصق)</option>
                <option value="shelf_talker">ملصق رف 10×3 سم</option>
                <option value="table_stand">ستاند ترابيزة A5</option>
                <option value="poster">بوستر A4</option>
                <option value="store_card">كارت المحل 9×5 سم</option>
            </select>
        </label>
        <div id="pick">
            <p class="muted">اختار المنتجات (فاضي = الكل، لحد {{ \App\Services\PrintSheetService::MAX_PRODUCTS }}).</p>
            @foreach ($products as $p)
                <label style="display:flex;gap:var(--s-2);padding:var(--s-1) 0"><input type="checkbox" name="product_ids[]" value="{{ $p->id }}"> {{ $p->name }}</label>
            @endforeach
        </div>
        <button class="btn">جهّز الملف PDF</button>
        <p class="muted">كل الأكواد أكبر من 2.5 سم عشان تتمسح بسهولة.</p>
    </form>
    @push('scripts')
    <script>
        var t = document.getElementById('tpl'), p = document.getElementById('pick');
        t.addEventListener('change', function () { p.hidden = t.value === 'store_card'; });
    </script>
    @endpush
@endsection
