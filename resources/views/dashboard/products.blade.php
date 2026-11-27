@extends('public.layout')
@section('title', 'المنتجات')
@section('content')
    <h1>المنتجات</h1>
    @if ($errors->any())<div class="notice">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ url('/dashboard/products/prices') }}">
        @csrf
        <div class="list">
            @foreach ($products as $p)
                <div style="display:flex;gap:8px;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)">
                    <span style="flex:1">{{ $p->name }}@if (! $p->is_visible) <span class="chip">مخفي</span>@endif</span>
                    <input name="prices[{{ $p->id }}]" inputmode="decimal" value="{{ $p->price }}" style="width:90px;padding:8px" aria-label="سعر {{ $p->name }}">
                    <a href="{{ url('/qr/'.$p->public_code.'.png') }}" download>QR</a>
                </div>
            @endforeach
        </div>
        <button class="btn" style="width:100%;border:0">حفظ كل الأسعار</button>
    </form>

    @foreach ($products as $p)
        <form method="POST" action="{{ url('/dashboard/products/'.$p->public_code.'/toggle') }}" style="display:inline">@csrf<button>{{ $p->is_visible ? 'إخفاء' : 'إظهار' }} {{ $p->name }}</button></form>
        <form method="POST" action="{{ url('/dashboard/products/'.$p->public_code) }}" style="display:inline">@csrf @method('DELETE')<button>حذف {{ $p->name }}</button></form><br>
    @endforeach

    <h2>منتج جديد</h2>
    <form method="POST" action="{{ url('/dashboard/products') }}">
        @csrf
        <input name="name" placeholder="اسم المنتج" required style="width:100%;padding:10px;margin:4px 0">
        <input name="price" inputmode="decimal" placeholder="السعر" style="width:100%;padding:10px;margin:4px 0">
        <input name="unit" placeholder="الوحدة (كيلو، لتر...)" style="width:100%;padding:10px;margin:4px 0">
        <select name="category_id" style="width:100%;padding:10px;margin:4px 0">
            <option value="">بدون قسم</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <button class="btn" style="width:100%;border:0">إضافة</button>
    </form>
@endsection
