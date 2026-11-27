@extends('public.layout')
@section('title', 'اعمل قائمتك')
@section('content')
    <h1>بيانات المحل</h1>
    <form method="POST" action="{{ url('/onboarding') }}">
        @csrf
        @php
            $field = fn ($n, $l, $t = 'text') => print('<label style="display:block;margin:10px 0">'.e($l).'<input name="'.$n.'" type="'.$t.'" value="'.e(old($n)).'" style="display:block;width:100%;padding:10px"></label>'.($errors->has($n) ? '<div style="color:#b91c1c">'.e($errors->first($n)).'</div>' : ''));
        @endphp
        @php $field('store_name', 'اسم المحل') @endphp
        @php $field('slug', 'رابط المحل (إنجليزي)') @endphp
        <label style="display:block;margin:10px 0">المحافظة
            <select name="governorate" style="display:block;width:100%;padding:10px">
                @foreach ($governorates as $g)<option value="{{ $g }}" @selected(old('governorate') === $g)>{{ $g }}</option>@endforeach
            </select>
        </label>
        <label style="display:block;margin:10px 0">النشاط
            <select name="business_type" style="display:block;width:100%;padding:10px">
                @foreach ($businessTypes as $b)<option value="{{ $b }}" @selected(old('business_type') === $b)>{{ $b }}</option>@endforeach
            </select>
        </label>
        @php $field('phone', 'رقم التليفون', 'tel') @endphp
        @php $field('whatsapp', 'واتساب (اختياري)', 'tel') @endphp
        @php $field('address', 'العنوان (اختياري)') @endphp
        <button class="btn" style="width:100%;border:0">كمّل</button>
    </form>
@endsection
