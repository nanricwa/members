@extends('member.layouts.auth')

@section('title', 'お支払い完了')

@section('content')
<div class="text-center">
    <div class="text-green-500 text-5xl mb-4">&#10003;</div>
    <h2 class="text-xl font-bold mb-2">お支払いが完了しました！</h2>
    <p class="text-gray-600 mb-6">
        決済が正常に処理されました。<br>
        プランが自動的に有効化されます。
    </p>

    @if($payment && $payment->isPaid())
        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <p class="text-sm text-gray-600">決済金額: <span class="font-bold">{{ $payment->formattedAmount() }}</span></p>
            @if($payment->plan)
                <p class="text-sm text-gray-600">プラン: <span class="font-bold">{{ $payment->plan->name }}</span></p>
            @endif
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-700">決済の確認処理中です。しばらくお待ちください。</p>
        </div>
    @endif

    <a href="{{ route('member.login') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
        ログインページへ
    </a>
</div>
@endsection
