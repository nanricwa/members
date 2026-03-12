@extends('member.layouts.auth')

@section('title', 'お支払いキャンセル')

@section('content')
<div class="text-center">
    <div class="text-gray-400 text-5xl mb-4">&#10007;</div>
    <h2 class="text-xl font-bold mb-2">お支払いがキャンセルされました</h2>
    <p class="text-gray-600 mb-6">
        決済処理がキャンセルされました。<br>
        再度お申し込みいただくことも可能です。
    </p>

    @if($form)
        <a href="{{ url('/register/' . $form->slug) }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            登録フォームに戻る
        </a>
    @else
        <a href="{{ route('member.login') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            ログインページへ
        </a>
    @endif
</div>
@endsection
