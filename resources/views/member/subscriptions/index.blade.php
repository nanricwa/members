@extends('member.layouts.app')

@section('title', 'サブスクリプション管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">サブスクリプション管理</h1>

    @if($subscriptions->isEmpty())
        <p class="text-gray-500">現在、有効なサブスクリプションはありません。</p>
    @else
        <div class="space-y-4">
            @foreach($subscriptions as $subscription)
                <div class="border rounded-lg p-5 {{ $subscription->isActive() ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">
                                {{ $subscription->plan?->name ?? '不明なプラン' }}
                            </h3>
                            <div class="mt-2 space-y-1 text-sm text-gray-600">
                                <p>
                                    ステータス:
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                        {{ $subscription->isActive() ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $subscription->isCancelled() ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $subscription->status->value === 'past_due' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $subscription->status->value === 'paused' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $subscription->status->label() }}
                                    </span>
                                </p>
                                @if($subscription->current_period_end)
                                    <p>次回更新日: {{ $subscription->current_period_end->format('Y年m月d日') }}</p>
                                @endif
                                @if($subscription->trial_ends_at && $subscription->isOnTrial())
                                    <p>トライアル終了: {{ $subscription->trial_ends_at->format('Y年m月d日') }}</p>
                                @endif
                                @if($subscription->cancelled_at)
                                    <p class="text-red-600">キャンセル日: {{ $subscription->cancelled_at->format('Y年m月d日') }}</p>
                                    @if($subscription->isOnGracePeriod())
                                        <p class="text-orange-600">※ {{ $subscription->current_period_end->format('Y年m月d日') }} まで引き続きご利用いただけます</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if($subscription->isActive() && !$subscription->cancelled_at)
                            <form method="POST" action="{{ route('member.subscriptions.cancel', $subscription) }}"
                                  onsubmit="return confirm('サブスクリプションをキャンセルしますか？\n現在の期間終了時にプランが停止されます。')">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition">
                                    キャンセル
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
