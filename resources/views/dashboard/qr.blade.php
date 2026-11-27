@extends('public.layout')
@section('title', 'أكواد QR')
@section('content')
    <h1>أكواد QR</h1>
    <p class="muted">هدف الكود الرئيسي دلوقتي: {{ $defaultUrl }}</p>
    <form method="GET" action="{{ url('/dashboard/qr/preview') }}" target="_blank">
        <label>الهدف
            <select name="target" style="width:100%;padding:8px;margin:6px 0">
                <option value="store">قائمة المحل</option>
                @foreach ($categories as $c)<option value="category:{{ $c->id }}">قسم: {{ $c->name }}</option>@endforeach
                @foreach ($products as $p)<option value="product:{{ $p->id }}">منتج: {{ $p->name }}</option>@endforeach
            </select>
        </label>
        <label>المقاس (بكسل) <input name="size" type="number" value="1024" min="256" max="2048"></label>
        <label>لون الكود <input name="fg" value="#000000"></label>
        <label>لون الخلفية <input name="bg" value="#FFFFFF"></label>
        <label><input type="checkbox" name="logo" value="1"> لوجو في النص</label>
        <label>الصيغة <select name="format"><option value="png">PNG</option><option value="svg">SVG</option></select></label>
        <p class="muted">أقل مقاس للطباعة: 2.5 سم. أصغر من كده ممكن ما يتمسحش.</p>
        <button class="btn" style="width:100%;border:0">عرض / تحميل</button>
    </form>
    <h2>غيّر هدف الكود الرئيسي</h2>
    <form method="POST" action="{{ url('/dashboard/qr/default') }}">@csrf
        <select name="target" style="width:100%;padding:8px;margin:6px 0">
            <option value="store">قائمة المحل</option>
            @foreach ($categories as $c)<option value="category:{{ $c->id }}">قسم: {{ $c->name }}</option>@endforeach
            @foreach ($products as $p)<option value="product:{{ $p->id }}">منتج: {{ $p->name }}</option>@endforeach
        </select>
        <button class="btn outline" style="width:100%">حفظ</button>
    </form>
@endsection
