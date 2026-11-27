@extends('marketing.layout')
@section('title', 'قائمة أسعارك على موبايل الزبون')
@section('body')
@php($faqs = config('faq.items'))
<section class="wrap-wide hero">
    <div>
        <h1>الزبون يمسح الكود، <em>ويشوف سعرك.</em></h1>
        <p class="lead">اعمل قائمة أسعارك مرة واحدة، واطبع كود QR وحطه على الرف أو الكاشير. الزبون يمسحه بكاميرا موبايله ويلاقي السعر قدامه، من غير تطبيق ولا تسجيل.</p>
        <div class="cta">
            <a class="m-btn" href="{{ url('/login') }}">اعمل قائمتك مجانًا</a>
            <a class="m-btn ghost" href="{{ url('/how-it-works') }}">شوف إزاي بتشتغل</a>
        </div>
        <p class="muted" style="margin-top:var(--s-3)">التسجيل بحساب جوجل في أقل من 3 دقايق.</p>
    </div>
    <div class="stage" aria-label="كود على الرف وصفحة السعر على الموبايل">
        <div class="shelf-card">
            <div class="muted">بقالة الأمانة</div>
            <b>زيت عباد الشمس 800 مل</b>
            <div class="qrbox">{!! $qrSvg !!}</div>
            <div class="muted">امسح لتشوف السعر</div>
        </div>
        <div class="phone">
            <div class="bar"></div>
            <div class="muted">بقالة الأمانة</div>
            <div class="name">زيت عباد الشمس 800 مل</div>
            <div class="pr">185 <small>جنيه</small></div>
            <span class="chip ok">متاح</span>
            <p class="muted" style="margin-top:var(--s-3)">آخر تحديث للسعر: النهارده</p>
        </div>
    </div>
</section>

<section class="proof">
    <div class="wrap-wide">
        <div><b>من غير تطبيق</b><span>كاميرا الموبايل تكفي</span></div>
        <div><b>من غير تسجيل</b><span>الزبون يفتح ويشوف</span></div>
        <div><b>كود ثابت للأبد</b><span>غيّر السعر والورقة زي ما هي</span></div>
    </div>
</section>

<section class="section band">
    <div class="wm" aria-hidden="true">المشكلة</div>
    <div class="wrap-wide">
        <h2>كل يوم نفس <em>السؤال</em></h2>
        <div class="bubbles">
            <div class="bubble them">بكام الزيت ده؟</div>
            <div class="bubble me">185 يا فندم.</div>
            <div class="bubble them">طيب والسمن اللي جنبه؟ والأرز؟</div>
            <div class="bubble me">لحظة أشوف السعر الجديد...</div>
        </div>
        <p style="max-width:34em;color:var(--ink-700);margin-top:var(--s-6)">السعر بيتغيّر كتير، والورق المكتوب بيتقدّم. وكل مرة بتجاوب فيها بتوقّف الكاشير. الزبون عايز يشوف السعر لوحده.</p>
    </div>
</section>

<section class="section">
    <div class="wm" aria-hidden="true">الحل</div>
    <div class="wrap-wide">
        <h2>الحل في <em>3 خطوات</em></h2>
        <ol class="steps" style="padding:0;max-width:640px">
            <li><div><b>سجّل وضيف منتجاتك</b>بحساب جوجل، وضيف الاسم والسعر. تقدر تعدّل 20 سعر في أقل من دقيقة من الموبايل.</div></li>
            <li><div><b>اطبع الكود</b>ملصق للرف، كارت للكاشير، أو بوستر للمحل. الاستوديو بيجهّز لك الورقة جاهزة للطباعة.</div></li>
            <li><div><b>غيّر السعر وقت ما تحب</b>الزبون بيشوف السعر الجديد في نفس اللحظة، والكود المطبوع يفضل شغال.</div></li>
        </ol>
    </div>
</section>

<section class="section band">
    <div class="wm" aria-hidden="true">المميزات</div>
    <div class="wrap-wide">
        <h2>اللي هتاخده <em>من أول يوم</em></h2>
        <div class="features">
            <div class="feature"><x-ui.icon name="qr-code" /><div><b>كود ثابت للأبد</b>غيّر الاسم أو السعر أو حتى القسم، والكود المطبوع ما بيبطّلش.</div></div>
            <div class="feature"><x-ui.icon name="zap" /><div><b>تعديل أسعار سريع</b>شاشة مخصوصة للأرقام: كيبورد أرقام، وحفظ أول ما تخرج من الخانة.</div></div>
            <div class="feature"><x-ui.icon name="printer" /><div><b>استوديو طباعة</b>ملصقات رف، كارت محل، بوستر A4 وستاند ترابيزة بمقاسات جاهزة.</div></div>
            <div class="feature"><x-ui.icon name="smartphone" /><div><b>بيفتح على أي موبايل</b>صفحة خفيفة تشتغل على النت الضعيف، من غير تطبيق.</div></div>
            <div class="feature"><x-ui.icon name="tag" /><div><b>تحكم في الظهور</b>اخفِ سعر منتج أو المنتج كله في ثانية، من غير ما الكود يبوظ.</div></div>
            <div class="feature"><x-ui.icon name="shield-check" /><div><b>بياناتك عندك</b>الزبون ما بيسجّلش، وإنت اللي بتتحكم في كل اللي بيظهر.</div></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="wm" aria-hidden="true">قبل وبعد</div>
    <div class="wrap-wide">
        <h2>قبل <em>وبعد</em></h2>
        <div class="ba">
            <div class="card before"><h3>قبل</h3><ul><li>ورق مكتوب بخط اليد وبيتقدّم.</li><li>الكاشير بيجاوب نفس السؤال طول اليوم.</li><li>تغيير سعر معناه تعديل كل ورقة.</li></ul></div>
            <div class="card"><h3>بعد</h3><ul><li>كود على الرف والزبون يشوف السعر لوحده.</li><li>الكاشير مشغول بالبيع.</li><li>سعر واحد بتغيّره من موبايلك وخلاص.</li></ul></div>
        </div>
    </div>
</section>

<section class="section band">
    <div class="wm" aria-hidden="true">الخطط</div>
    <div class="wrap-wide">
        <h2>الخطط <em>والأسعار</em></h2>
        @include('marketing._plans', ['plans' => $plans])
        <p class="muted" style="margin-top:var(--s-3)">الأسعار بتتحدّث تلقائيًا من لوحة الإدارة.</p>
    </div>
</section>

<section class="section">
    <div class="wm" aria-hidden="true">أسئلة</div>
    <div class="wrap-wide" style="max-width:760px">
        <h2>أسئلة <em>شائعة</em></h2>
        @foreach (array_slice($faqs, 0, 4) as [$q, $a])
            <details class="accordion"><summary>{{ $q }}</summary><p>{{ $a }}</p></details>
        @endforeach
        <p><a href="{{ url('/faq') }}" style="color:var(--brand)">كل الأسئلة</a></p>
    </div>
</section>

<section class="cta-band">
    <div class="wrap-wide">
        <h2>خلّي سعرك معلن النهارده.</h2>
        <p style="margin:0 0 var(--s-6)">اعمل قائمتك المجانية وطبع أول كود قبل ما اليوم يخلص.</p>
        <a class="m-btn" href="{{ url('/login') }}">ابدأ مجانًا</a>
    </div>
</section>
@endsection
