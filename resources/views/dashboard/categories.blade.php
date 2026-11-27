@extends('layouts.merchant')
@section('title', 'الأقسام')
@section('content')
    <h1>الأقسام</h1>
    @forelse ($categories as $c)
        <form class="card flat" style="display:flex;align-items:center;gap:var(--s-3);margin-bottom:var(--s-2)" method="POST" action="{{ url('/dashboard/categories/'.$c->id) }}">@csrf @method('DELETE')
            <b style="flex:1;color:var(--ink)">{{ $c->name }}</b>
            <button class="btn outline sm inline" style="margin:0" onclick="return confirm('تمسح القسم؟ المنتجات هتفضل.')">حذف</button>
        </form>
    @empty
        <div class="empty">مفيش أقسام. الأقسام اختيارية، بتفيد لو عندك منتجات كتير.</div>
    @endforelse
    <form class="card" style="margin-top:var(--s-4)" method="POST" action="{{ url('/dashboard/categories') }}">@csrf
        <label class="field">اسم القسم جديد<input name="name" required></label>
        <button class="btn">إضافة</button>
    </form>
@endsection
