@extends('marketing.layout')
@section('title', 'تواصل')
@section('body')
<section class="section">
    <div class="wrap-wide prose">
        <h1>تواصل معانا</h1>
        @if ($whatsapp || $email)
            <p>
                @if ($whatsapp)<a class="m-btn" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" rel="noopener">واتساب</a>@endif
                @if ($email)<a class="m-btn ghost" href="mailto:{{ $email }}">{{ $email }}</a>@endif
            </p>
        @else
            <div class="empty">بيانات التواصل لسه ما اتضافتش. الأدمن بيضيفها من الإعدادات (contact_whatsapp / contact_email).</div>
        @endif
    </div>
</section>
@endsection
