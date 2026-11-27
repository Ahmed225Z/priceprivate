@extends('marketing.layout')
@section('title', 'دليل المكوّنات')
@section('robots', 'noindex,nofollow')
@section('body')
<section class="section sg"><div class="wrap-wide" style="max-width:760px">
<h1>دليل المكوّنات</h1>
<h2>الألوان</h2><div class="swatches"><div class="sw"><i style="background:var(--brand)"></i><span>brand</span></div><div class="sw"><i style="background:var(--brand-700)"></i><span>brand-700</span></div><div class="sw"><i style="background:var(--brand-100)"></i><span>brand-100</span></div><div class="sw"><i style="background:var(--brand-50)"></i><span>brand-50</span></div><div class="sw"><i style="background:var(--ink)"></i><span>ink</span></div><div class="sw"><i style="background:var(--ink-900)"></i><span>ink-900</span></div><div class="sw"><i style="background:var(--muted-3)"></i><span>muted</span></div><div class="sw"><i style="background:var(--surface-2)"></i><span>surface-2</span></div><div class="sw"><i style="background:var(--surface-3)"></i><span>surface-3</span></div><div class="sw"><i style="background:var(--border)"></i><span>border</span></div><div class="sw"><i style="background:var(--accent)"></i><span>accent</span></div><div class="sw"><i style="background:var(--ok)"></i><span>ok</span></div><div class="sw"><i style="background:var(--warn)"></i><span>warn</span></div><div class="sw"><i style="background:var(--danger)"></i><span>danger</span></div><div class="sw"><i style="background:var(--info)"></i><span>info</span></div></div>
<h2>الأزرار</h2>
<p><a class="m-btn" href="#">أساسي</a> <a class="m-btn ghost" href="#">ثانوي</a></p>
<button class="btn" type="button">زر التطبيق</button><button class="btn outline" type="button">زر بإطار</button>
<h2>الشارات</h2>
<p><span class="chip">عادي</span> <span class="chip ok">متاح</span> <span class="chip warn">قديم</span> <span class="chip danger">مخفي</span> <span class="chip badge">الأكثر طلبًا</span></p>
<h2>الحقول</h2>
<label class="field">اسم المنتج<input placeholder="زيت عباد الشمس"><span class="hint">اكتب الاسم زي ما الزبون بيعرفه</span></label>
<h2>كرت وتنبيه</h2>
<div class="card">كرت بحدود 1px وظل رمادي.</div><div class="notice">تنبيه عادي.</div>
<h2>أكورديون</h2>
<details class="accordion"><summary>سؤال</summary><p>إجابة.</p></details>
<h2>جدول</h2>
<table class="table"><tr><th>المنتج</th><th>السعر</th></tr><tr><td>أرز 1 كيلو</td><td class="tnum">42.50</td></tr></table>
<h2>حالة فاضية وSkeleton</h2>
<div class="empty">مفيش بيانات لسه.</div><div class="skeleton" style="height:24px;margin-top:var(--s-3)"></div>
<h2>نافذة</h2>
<button class="btn outline" type="button" onclick="document.getElementById('m').showModal()">افتح النافذة</button>
<dialog class="modal" id="m"><p>نافذة بسيطة بحدود وظل.</p><form method="dialog"><button class="btn">تمام</button></form></dialog>
</div></section>
@endsection
