@extends('layouts.merchant')
@section('title', 'الاشتراك')
@section('content')
    <h1>الاشتراك</h1>
    <p>خطتك الحالية: <strong>{{ $plan->name }}</strong></p>
    @if ($subscription && $subscription->status === 'grace')
        <div class="notice">اشتراكك خلص. المميزات شغالة لحد {{ $subscription->grace_ends_at?->format('Y-m-d') }}. جدّد عشان متفقدهاش.</div>
    @endif
    @if ($subscription && $paid)
        <p class="muted">بينتهي في {{ $subscription->ends_at?->format('Y-m-d') }}</p>
    @endif
    <form method="POST" action="{{ url('/dashboard/subscription/upgrade') }}">@csrf
        <button class="btn" style="width:100%;border:0">{{ $paid ? 'تجديد' : 'ترقية' }}</button>
    </form>
    @if ($invoices->isNotEmpty())
        <h2>الفواتير</h2>
        @foreach ($invoices as $i)<a href="{{ url('/dashboard/invoices/'.$i->id) }}">{{ $i->number }}</a><br>@endforeach
    @endif
@endsection
