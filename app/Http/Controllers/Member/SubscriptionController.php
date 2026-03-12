<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * サブスクリプション一覧
     */
    public function index(): View
    {
        $member = Auth::guard('member')->user();
        $subscriptions = $member->subscriptions()->with('plan')->latest()->get();

        return view('member.subscriptions.index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * サブスクリプションキャンセル（期間終了時に停止）
     */
    public function cancel(Subscription $subscription): RedirectResponse
    {
        $member = Auth::guard('member')->user();

        // 自分のサブスクリプションのみキャンセル可能
        if ($subscription->member_id !== $member->id) {
            abort(403);
        }

        if (!$subscription->isActive()) {
            return back()->withErrors(['subscription' => 'このサブスクリプションはキャンセルできません。']);
        }

        try {
            $gateway = PaymentGatewayFactory::create($subscription->gateway);
            $cancelled = $gateway->cancelSubscription($subscription);

            if ($cancelled) {
                $subscription->update([
                    'cancelled_at' => now(),
                ]);

                return redirect()->route('member.subscriptions')
                    ->with('success', 'サブスクリプションのキャンセルをリクエストしました。現在の期間終了時に停止されます。');
            }

            return back()->withErrors(['subscription' => 'キャンセル処理に失敗しました。']);
        } catch (\Exception $e) {
            Log::error('Subscription cancel failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['subscription' => 'キャンセル処理中にエラーが発生しました。']);
        }
    }
}
