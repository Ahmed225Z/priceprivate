@if ($slot)
    {{-- Space is reserved with min-height so the page never shifts (CLS = 0). --}}
    <aside aria-label="إعلان" style="min-height:{{ $slot->min_height }}px;margin:12px 0;overflow:hidden">
        @if ($slot->type === 'direct' && $slot->banner_path)
            <a href="{{ $slot->target_url }}" rel="sponsored noopener" target="_blank">
                <img src="{{ asset('storage/'.$slot->banner_path) }}" alt="إعلان" style="width:100%;display:block">
            </a>
        @elseif ($slot->type === 'adsense' && $slot->adsense_code)
            {{-- Admin-controlled AdSense snippet only; merchants can never write to this field. --}}
            {!! $slot->adsense_code !!}
        @endif
    </aside>
@endif
