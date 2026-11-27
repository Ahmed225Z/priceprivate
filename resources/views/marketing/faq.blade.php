@extends('marketing.layout')
@section('title', 'أسئلة شائعة')
@section('body')
@php($faqs = config('faq.items'))
<section class="section">
    <div class="wm" aria-hidden="true">أسئلة</div>
    <div class="wrap-wide" style="max-width:760px">
        <h2>أسئلة <em>شائعة</em></h2>
        @foreach ($faqs as [$q, $a])
            <details class="accordion"><summary>{{ $q }}</summary><p>{{ $a }}</p></details>
        @endforeach
        <p style="margin-top:var(--s-6)">ملقتش إجابتك؟ <a href="{{ url('/contact') }}" style="color:var(--brand)">تواصل معانا</a>.</p>
    </div>
</section>
@endsection
