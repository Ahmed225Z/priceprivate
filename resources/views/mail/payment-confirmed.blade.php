<!DOCTYPE html>
<html lang="ar" dir="rtl">
<body style="margin:0;background:#f6f6f6;font-family:Tahoma,Arial,sans-serif;color:#263238">
<div style="max-width:520px;margin:0 auto;background:#ffffff;padding:24px;border:1px solid #e6e8ec;border-radius:12px;text-align:right">
    <h2 style="color:#243858;margin:0 0 12px">اتأكد دفعك يا {{ $merchant->store_name }}</h2>
    <p>خطتك <strong>{{ $plan->name }}</strong> اتفعّلت{{ $subscription?->ends_at ? ' لحد '.$subscription->ends_at->format('Y-m-d') : '' }}.</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0">
        <tr><td style="padding:8px;border:1px solid #e6e8ec">المبلغ</td><td style="padding:8px;border:1px solid #e6e8ec">{{ $amount }}</td></tr>
        <tr><td style="padding:8px;border:1px solid #e6e8ec">رقم الفاتورة</td><td style="padding:8px;border:1px solid #e6e8ec">{{ $number }}</td></tr>
    </table>
    <p>الفاتورة مرفقة PDF. تقدر تنزّلها كمان من صفحة الاشتراك.</p>
    <p><a href="{{ $dashboardUrl }}" style="display:inline-block;background:#029a8e;color:#ffffff;padding:10px 18px;border-radius:8px;text-decoration:none">صفحة الاشتراك</a></p>
</div>
</body>
</html>
