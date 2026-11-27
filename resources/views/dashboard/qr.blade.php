@extends('layouts.merchant')
@section('title', 'أكواد QR')
@section('content')
    <h1>أكواد QR</h1>
    <p class="muted">هدف الكود الرئيسي دلوقتي: <span dir="ltr">{{ $defaultUrl }}</span></p>
    <div class="card">
        <div class="qr-preview"><img id="qr-img" alt="معاينة الكود" src=""></div>
        <form id="qr-form" method="GET" action="{{ url('/dashboard/qr/preview') }}" target="_blank">
            <label class="field">الهدف
                <select name="target" id="f-target">
                    <option value="store">قائمة المحل</option>
                    @foreach ($categories as $c)<option value="category:{{ $c->id }}">قسم: {{ $c->name }}</option>@endforeach
                    @foreach ($products as $p)<option value="product:{{ $p->id }}">منتج: {{ $p->name }}</option>@endforeach
                </select>
            </label>
            <label class="field">لون الكود<input type="color" name="fg" value="#000000" style="padding:2px;height:44px"></label>
            <label class="field">لون الخلفية<input type="color" name="bg" value="#ffffff" style="padding:2px;height:44px"></label>
            <label class="field">المقاس (بكسل)<input name="size" type="number" value="1024" min="256" max="2048"></label>
            <label class="field">الصيغة<select name="format"><option value="png">PNG</option><option value="svg">SVG</option></select></label>
            <label class="field"><input type="checkbox" name="logo" value="1" style="display:inline;width:auto"> لوجو في النص</label>
            <p class="muted">أقل مقاس للطباعة: 2.5 سم. أصغر من كده ممكن ما يتمسحش. استخدم لون غامق على خلفية فاتحة.</p>
            <button class="btn">تحميل بالمقاس ده</button>
        </form>
    </div>
    <h2 style="font-size:18px;margin-top:var(--s-6)">غيّر هدف الكود الرئيسي</h2>
    <form class="card" method="POST" action="{{ url('/dashboard/qr/default') }}">@csrf
        <label class="field">الهدف الجديد
            <select name="target">
                <option value="store">قائمة المحل</option>
                @foreach ($categories as $c)<option value="category:{{ $c->id }}">قسم: {{ $c->name }}</option>@endforeach
                @foreach ($products as $p)<option value="product:{{ $p->id }}">منتج: {{ $p->name }}</option>@endforeach
            </select>
        </label>
        <p class="muted">الكود المطبوع ما بيتغيّرش. بس هدفه هو اللي بيتبدّل.</p>
        <button class="btn outline">حفظ</button>
    </form>
    @push('scripts')
    <script>
        (function () {
            var f = document.getElementById('qr-form'), img = document.getElementById('qr-img'), t;
            function update() {
                var q = new URLSearchParams(new FormData(f));
                q.set('format', 'png'); q.set('size', '440'); q.delete('logo');
                if (f.logo.checked) q.set('logo', '1');
                img.src = f.action + '?' + q.toString();
            }
            f.addEventListener('input', function () { clearTimeout(t); t = setTimeout(update, 250); });
            update();
        })();
    </script>
    @endpush
@endsection
