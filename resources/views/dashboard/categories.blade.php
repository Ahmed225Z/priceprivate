@extends('public.layout')
@section('title', 'الأقسام')
@section('content')
    <h1>الأقسام</h1>
    @foreach ($categories as $c)
        <form method="POST" action="{{ url('/dashboard/categories/'.$c->id) }}">@csrf @method('DELETE')
            {{ $c->name }} <button>حذف</button>
        </form>
    @endforeach
    <form method="POST" action="{{ url('/dashboard/categories') }}">@csrf
        <input name="name" placeholder="اسم القسم" required style="padding:10px"><button class="btn" style="border:0">إضافة</button>
    </form>
@endsection
