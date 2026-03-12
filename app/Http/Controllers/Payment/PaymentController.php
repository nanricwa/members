<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RegistrationForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Stripe Checkoutからの成功リダイレクト先
     * ※実際の決済確認はWebhookで行われる
     */
    public function success(Request $request): View
    {
        $sessionId = $request->query('session_id');
        $payment = null;

        if ($sessionId) {
            $payment = Payment::where('gateway_payment_id', $sessionId)->first();
        }

        return view('member.payment.success', [
            'payment' => $payment,
        ]);
    }

    /**
     * 決済キャンセル時のリダイレクト先
     */
    public function cancel(Request $request): View
    {
        $formSlug = $request->query('form');
        $form = $formSlug ? RegistrationForm::where('slug', $formSlug)->first() : null;

        return view('member.payment.cancel', [
            'form' => $form,
        ]);
    }
}
