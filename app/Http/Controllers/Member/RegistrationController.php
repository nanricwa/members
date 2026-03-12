<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\RegistrationForm;
use App\Services\EmailService;
use App\Services\MemberRegistrationService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function show(RegistrationForm $form)
    {
        if (!$form->is_active || !$form->isAccepting()) {
            abort(404, 'このフォームは現在受付していません。');
        }

        $customFields = $form->customFields()->get();

        return view('member.auth.register', compact('form', 'customFields'));
    }

    public function store(RegistrationForm $form, Request $request, MemberRegistrationService $service)
    {
        if (!$form->is_active || !$form->isAccepting()) {
            abort(404, 'このフォームは現在受付していません。');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|confirmed|min:8',
        ];

        // カスタムフィールドのバリデーション
        foreach ($form->customFields as $field) {
            $fieldRules = [];
            if ($field->pivot->is_required || $field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            $rules["custom_fields.{$field->slug}"] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $member = $service->register($form, $validated);

        // 有料フォームの場合: Stripe Checkout にリダイレクト
        if (!$form->isFree()) {
            try {
                $paymentService = app(PaymentService::class);
                $checkoutUrl = $paymentService->initiateFormPayment($member, $form);

                return redirect()->away($checkoutUrl);
            } catch (\Exception $e) {
                Log::error('Payment initiation failed', [
                    'member_id' => $member->id,
                    'form_id' => $form->id,
                    'error' => $e->getMessage(),
                ]);

                // 決済開始に失敗した場合、メンバーを削除してエラーを返す
                $member->forceDelete();

                return back()->withErrors([
                    'payment' => '決済の開始に失敗しました。しばらくしてからもう一度お試しください。',
                ])->withInput();
            }
        }

        // 無料フォームの場合: 完了メール送信 + ログインして完了画面へ
        app(EmailService::class)->sendRegistrationCompletion($member, $form);

        Auth::guard('member')->login($member);

        if ($form->redirect_url) {
            return redirect($form->redirect_url);
        }

        return redirect()->route('registration.complete', $form->slug);
    }

    public function complete(RegistrationForm $form)
    {
        return view('member.auth.register-complete', compact('form'));
    }
}
