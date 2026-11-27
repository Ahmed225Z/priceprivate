<div class="plans">
    @foreach ($plans as $plan)
        @php
            $paid = (float) $plan->price > 0;
            $feat = ['no_ads' => 'من غير إعلانات', 'custom_color' => 'لون مخصص لقائمتك', 'verified_badge' => 'شارة «موثّق»', 'priority_support' => 'دعم أولوية'];
        @endphp
        <div class="plan {{ $paid ? 'hot' : '' }}">
            <h3 style="margin:0 0 var(--s-2)">{{ $plan->name }} @if ($paid)<span class="chip badge">الأكثر طلبًا</span>@endif</h3>
            <div class="amount">{{ $paid ? \App\Support\PriceFormatter::format((string) $plan->price) : 'مجانًا' }} @if ($paid)<small>{{ $plan->currency === 'EGP' ? 'جنيه' : $plan->currency }} / سنة</small>@endif</div>
            <ul>
                <li><x-ui.icon name="check" :size="18" />{{ $plan->product_limit === null ? 'منتجات غير محدودة' : 'لحد '.$plan->product_limit.' منتج' }}</li>
                <li><x-ui.icon name="check" :size="18" />أكواد QR وكل قوالب الطباعة</li>
                @foreach ((array) $plan->features as $f)
                    @if (isset($feat[$f]))<li><x-ui.icon name="check" :size="18" />{{ $feat[$f] }}</li>@endif
                @endforeach
            </ul>
            <a class="m-btn {{ $paid ? '' : 'ghost' }}" href="{{ url('/login') }}">ابدأ دلوقتي</a>
        </div>
    @endforeach
</div>
