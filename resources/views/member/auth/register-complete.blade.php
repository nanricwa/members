@extends('member.layouts.auth')

@section('title', '登録完了')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8 text-center">
    <div class="text-green-500 mb-4">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>

    <h2 class="text-xl font-bold text-gray-800 mb-4">登録が完了しました！</h2>

    @if($form->thanks_message)
        <div class="text-gray-600 mb-6">{!! nl2br(e($form->thanks_message)) !!}</div>
    @else
        <p class="text-gray-600 mb-6">ご登録ありがとうございます。マイページからコンテンツにアクセスできます。</p>
    @endif

    <a href="{{ route('member.dashboard') }}"
       class="inline-block bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition font-medium">
        マイページへ
    </a>
</div>
@endsection
